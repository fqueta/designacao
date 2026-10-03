# Documentação do Projeto — Designação (DVM)

> Sistema de designações de estudantes para reuniões Vida e Ministério (JW).
> Última atualização: 03/10/2026. Inclui o CRUD `links_semanas` e a automação do Sincronizar com JW.

## 1. Visão geral

- **Framework:** Laravel 11 + PHP (ver `composer.json`), views Blade com tema AdminLTE (`jeroennoten/laravel-adminlte`), JS próprio em `public/js/lib.js`.
- **Banco:** MySQL. `.env` local: `DB_HOST=127.0.0.1 DB_PORT=3307 DB_DATABASE=maisaqu_gerdes`.
- **Multi-tenant por congregação:** existe o banco **central** (`maisaqu_gerdes`, tabela `congregacaos`) e um banco **tenant por congregação** (ex.: `ba` acessado via `ba.localhost:7008`). A conexão ativa é trocada por request — ver seção 2.
- **Domínio:** programas semanais (`meio-semana`, `fim-semana`), designações por parte (`designations`), publicadores/estudantes (`publicadores`), tipos de parte (`tags`), configurações (`qoptions`, `documentos`).

## 2. Multi-tenancy (ler antes de mexer no banco)

- `app/Http/Middleware/TenancyMiddleware.php`: lê o subdomínio (`Qlib::is_subdominio()`), busca a congregação em `congregacao::where('usuario', $subdominio)` e chama `Qlib::selectDefaultConnection('tenant', $arr_config)` — a partir daí **todos os Models usam o banco do tenant**.
- `app/Console/Commands/TenancyMigrateCommand.php`: `php artisan tenancy:migrate {usuario}` roda as migrations de `database/migrations/tenant` no banco do tenant. `TenancySeedCommand` faz o equivalente para seeders.
- **Migrations duplicadas:** quase toda tabela tem 2 arquivos — `database/migrations/XXXX_nome.php` (central) e `database/migrations/tenant/XXXX_nome.php` (tenant). Ao criar tabela nova, criar nos 2 lugares. As de correção idempotente usam `if (!Schema::hasTable(...))` (precedente: `2025_11_04_000001_create_links_semanas_table`).
- **Menus/permissões são por banco:** `menus` e `permissions` existem no central e em cada tenant. Mudança de menu precisa de migration nos 2 lugares (precedente: `2026_10_03_000001_add_links_semanas_menu`).

## 3. Estrutura relevante

```
app/Http/Controllers/            DocumentosController, FamiliaController, ... (CRUDs raiz)
app/Http/Controllers/admin/      designaController (sincronizar), PostController (programas),
                                 QoptionsController, TagsController, UserPermissions, EventController, GetProgramController
app/Http/Controllers/Api/        VmpController (scraping WOL -> partes)
app/Http/Controllers/            ApostilaController (scraping jw.org, uso parcial), LinksSemanasController
app/Services/                    LinkSemanaResolver (auto-descoberta de links JW)
app/Models/                      LinkSemana, designation, Post, Publicador, Tag, Qoption, Menu, Permission, ...
app/Qlib/Qlib.php                Helper central: formulario(), listaTabela(), qForm(), buscaValorDb0(),
                                 numero_semana(), link_programacao_woljw(), ver_PermAdmin(), ...
resources/views/padrao/          index.blade.php, createedit.blade.php (CRUD genérico reutilizado)
resources/views/programa/        createedit.blade.php (botões Sincronizar), edit_programas_semanais.blade.php
resources/views/qlib/            formulario.blade.php, campos_form.blade.php, listaTabela.blade.php
routes/web.php                   tudo dentro de Route::middleware(['web', TenancyMiddleware::class])
config/database.php              conexões mysql (central) e tenant
```

## 4. Padrão CRUD do projeto (seguir ao criar novos)

Todo CRUD (`documentos`, `tags`, `qoptions`, `links-semanas`) segue o mesmo molde:

1. **Model** em `app/Models` com `$fillable`. Se a tabela não tem `created_at/updated_at`, declarar `public $timestamps = false`. Se não tem `excluido/deletado/ativo`, **não** filtrar por eles na query (diferente dos CRUDs antigos).
2. **Controller** com: `$routa` (igual ao `url` do menu), `$label`, `$view = 'padrao'`, `$tab` (nome físico da tabela p/ `EventController`), métodos `queryX()`, `campos()`, `index/create/store/edit/update/destroy`, suporte a `ajax=s` (JSON) e `filter/limit/order/campo_order` via `$_GET`.
3. **`campos()`** — formato: `'campo' => ['label'=>..., 'active'=>bool, 'type'=>text|date|url|number|select|textarea|hidden|chave_checkbox, 'exibe_busca'=>..., 'event'=>..., 'tam'=>1-12, ...]`. Tipos `date/url/number` caem no `@else` genérico de `qlib/campos_form.blade.php` (input HTML nativo). Validação no `store/update` via `$request->validate()`.
4. **Rota:** `Route::resource('minha-rota','\App\Http\Controllers\MeuController',['parameters'=>['minha-rota'=>'id']])` dentro do grupo `tenant.auth` em `routes/web.php`.
5. **Menu + permissão:** inserir em `menus` (`url` = routa, `route` = `routa.index`, `pai` = `config` p/ área Configurações) e adicionar `routa => 's'` em `ler/ler_arquivos/create/update/delete` no JSON `permissions.id_menu` **só** dos perfis que já têm `documentos` (não liberar p/ perfis restritos). Fazer via migration central + tenant.
6. **Autorização:** `authorize('ler|create|update|delete', $this->routa)` → Gates em `app/Providers/AuthServiceProvider.php` → `Qlib::ver_PermAdmin()` lê `permissions.id_menu` do `id_permission` do usuário. Se faltar a chave, dá 403 — debugar por aí primeiro.

## 5. Fluxo "Sincronizar com JW" (ponta a ponta)

```
programa/createedit.blade.php (botão 1)
  -> public/js/lib.js sinc_partes_jw() [data-semanas + type=jw]
  -> POST /ajax/sinc-partes-jw (routes/web.php:50)
  -> designaController@sinc_partes (decodeArray dados base64)
  -> add_designacao($datas, $type)
     -> LinkSemanaResolver::resolve($data)   [seção 6]
     -> VmpController@gera_api($link, $data)  [scraping WOL, seletores #p1, h3.du-color--*]
     -> inserir_parte($vp)                   [dedup por data+numero, ignora ajudante id 28]
```

- `type=inic_fim` (botão 2) insere partes fixas início/fim, não usa JW.
- `add_designacao` aceita array de segundas-feiras ou string única; tem `sleep(2)` anti-bloqueio entre semanas.
- `Qlib::link_programacao_woljw($data)` = leitura direta `links_semanas` por `data` (override manual). O Resolver chama isso como fallback.

## 6. Tabela `links_semanas` + `LinkSemanaResolver`

- **Tabela:** `id, data DATE, link VARCHAR(255)`. Sem timestamps, sem `excluido/deletado/ativo`.
- **CRUD:** `/links-semanas` (menu Configurações → "Links JW (Semanas)"). Arquivos: `app/Models/LinkSemana.php`, `app/Http/Controllers/LinksSemanasController.php`, rota resource em `routes/web.php`, migrations `2026_10_03_000001_add_links_semanas_menu` (central + tenant).
- **Intervalo mínimo por parte:** tela do publicador (`config_designacao.blade.php`) tem coluna "Intervalo" (`config.designacao.intervalo_{id}`, 0–4 meses; a antiga coluna "Ultima" foi removida — valores `ultima_*` nunca eram lidos). `list_participants(..., $data_ref)` marca `em_intervalo`/`intervalo_ate` comparando `ultima_desta.data + N meses` com a data da semana (modal mostra selo amarelo, manual continua liberado); `ordenarPorAntiguidade()` joga intervalados para o fim; `designar_auto` pula intervalados. A data chega via `GET /ajax/list-participantes?data=Y-m-d` (JS extrai de `des2[DATA]...`).
- **Alternância e ocupação:** `list_participants` marca `repetiu_recente`/`repetiu_ha` (mesma parte dentro de `qoption('alternancia_semanas')`, padrão 4 semanas), `vezes_6m` (COUNT bulk por parte, desempate de variedade) e `ja_nesta_data` {numero, parte} (bulk em `designations` data=X, ambos os papéis + espelhos 28). Ordenação: livres → nunca-fez → antiguidade → variedade → tiebreaks; bloqueados no fim. Modal: selos vermelho/azul + `confirm()` em `select_m_paraticipante` via `data-avisos`. `designar_auto` pula bloqueados com aviso motivado (`motivosBloqueio`/`textoMotivos`).
- **Performance do auto:** `list_participants` tem cache estático por request (chave com id efetivo — ajudante 28 reaproveita), `confere_ultima_parte` sincroniza 1x por pessoa, modo `$resumido` no auto (sem confere, `limit` 1) e `set_time_limit(180)` no endpoint. Medido no tenant ba: 41s → ~24s por semana cheia.
- **Designação automática:** botão "(3) Designar automaticamente" no card de cada semana (`edit_programas_semanais.blade.php` + `designar_auto()` em `lib.js`) → `POST /ajax/designar-auto` → `designaController@designar_auto` (só vagas vazias da semana, sem repetir pessoa, ajudante pref. mesmo sexo, espelhos 28 gerados no save).
- **Resolver** (`app/Services/LinkSemanaResolver.php::resolve($data Y-m-d): ?string`):
  1. Banco primeiro — só link contendo `/wol/d/` vale como cache (manual tem prioridade; links `/meetings/` legados são ignorados e regravados).
  2. Data passada sem link → não inventa (retorna banco/fallback).
  3. Hoje/futura sem link → `discoverViaLibrary()`: raspa `https://wol.jw.org/pt/wol/library/r5/lp-t/todas-as-publicações/apostilas/apostila-vida-e-ministério-{ano}/{mês-slug}` (Guzzle + `symfony/dom-crawler`, já dependências), casa o texto da semana (`parseWeekText()` entende `2-8 de novembro`, `30 de novembro–6 de dezembro`, `28 de dezembro de 2026–3 de janeiro de 2027` — 1ª data = segunda) com `$data`; tenta o mês da data e depois o mês anterior (semanas de dezembro aparecem na página de novembro); valida o `/d/` (HTTP 200 + marcas `vida e minist/tesouros/apostila`) e grava `updateOrCreate`.
- **Por que não gerar `/meetings/{ano}/{semana}`:** abre página de índice que o `gera_api` não extrai (`total=0`). Tentativa anterior fez isso e foi descartada.
- **Limite real:** só funciona p/ meses que a WOL já publicou (verificado: até dez/2026; jan/2027+ retorna null até a organização publicar). Sem link, o sync registra `Sem link JW para a data X` em vez de falhar silencioso.

## 7. Comandos operacionais

```powershell
php artisan migrate                          # central
php artisan migrate:status                   # conferir batches
php artisan tenancy:migrate ba               # tenant da congregação 'ba' (troque o usuário)
php artisan route:list --name=links-semanas  # conferir rotas do CRUD
php -l app/Services/LinkSemanaResolver.php   # sintaxe
# Teste de parse/descoberta (scripts temporários em $env:TEMP\opencode, apagar depois):
php artisan tinker --execute='...'           # cuidado: aspas no PowerShell; prefira script com bootstrap/app.php + Kernel::bootstrap()
```

Logs de falha do resolver: `storage/logs/laravel.log` (`LinkSemanaResolver: ...`).

## 8. Pegadinhas — status (03/10/2026: quase todas resolvidas)

- [x] `Qlib.php` emitia `DEPRECATED` (parâmetro opcional antes de obrigatório em `dados_tab()` e `valorTabDb()`). **Resolvido:** defaults `= null` adicionados — chamadas existentes passam todos os args, comportamento idêntico.
- [x] `link_programacao_woljw()` fazia `str_replace('{ano}/{semana}')` inútil p/ links `/d/`. **Resolvido:** early-return quando o link contém `/wol/d/` (+ guard `is_string` p/ `$tl=false` do banco vazio).
- [x] Virada de ano (`dia>29 && semana>50 => ano++` errava ex.: `2025-12-29` → `/2025/1`). **Resolvido:** usa `date('o', strtotime($data))` (ano ISO-8601) em `link_programacao_woljw()` e `link_programacao_jworg()` — verificado: `2025-12-29 → /2026/1`, `2021-01-01 → /2020/53`. (Na prática o Resolver nem usa esse template, só a página da biblioteca.)
- [x] Throttle da WOL em syncs de N semanas. **Amenizadado:** página mensal cacheada em arquivo por 12h (`wol_lib_{ano}_{mes}`); sync de 8 semanas do mesmo mês = 1 download. Falhas 404 (apostila não publicada) **não** são cacheadas. Mantidos `sleep()` e timeouts.
- [ ] `TesteController@index` tem chamadas comentadas de debug — inofensivo (rota exige auth `tenant.auth`), manter como referência.
