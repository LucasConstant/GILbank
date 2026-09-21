<script>
  import { onMount } from 'svelte';

  const appStorageKey = 'gilbank_token';

  let token = $state(localStorage.getItem(appStorageKey) ?? '');
  let user = $state(null);
  let account = $state(null);
  let investments = $state([]);
  let statement = $state([]);
  let error = $state('');
  let success = $state('');
  let isLoading = $state(false);
  let isReady = $state(false);

  let loginForm = $state({
    email: 'ana.cliente@gilbank.local',
    password: 'password',
  });

  let pixForm = $state({
    conta_destino_id: '',
    valor: '',
    descricao: '',
  });

  let applyForm = $state({
    tipo: 'cdb',
    valor: '',
  });

  let redeemForm = $state({
    tipo: 'cdb',
    valor: '',
  });

  let period = $state({
    start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
    end_date: new Date().toISOString().slice(0, 10),
  });

  const currency = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });

  const formatMoney = (value) => {
    const number = Number(value ?? 0);
    return Number.isFinite(number) ? currency.format(number) : currency.format(0);
  };

  const resetFeedback = () => {
    error = '';
    success = '';
  };

  const authHeaders = (extra = {}) => {
    const headers = new Headers(extra);
    headers.set('Accept', 'application/json');
    if (token) {
      headers.set('Authorization', `Bearer ${token}`);
    }
    return headers;
  };

  async function apiFetch(path, options = {}) {
    const headers = authHeaders(options.headers ?? {});
    const method = options.method ?? 'GET';
    const body = options.body;

    if (body && !(body instanceof FormData) && !headers.has('Content-Type')) {
      headers.set('Content-Type', 'application/json');
    }

    const response = await fetch(`/api${path}`, {
      ...options,
      headers,
      body: body && typeof body !== 'string' ? JSON.stringify(body) : body,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(payload.message || payload.errors?.email?.[0] || 'Operação falhou.');
    }

    return payload;
  }

  async function loadMe() {
    if (!token) {
      isReady = true;
      return;
    }

    try {
      const payload = await apiFetch('/me');
      user = payload.user;
      account = payload.account;
      await loadDashboard();
      await loadStatement();
    } catch (err) {
      error = err.message;
      token = '';
      user = null;
      account = null;
    } finally {
      isReady = true;
    }
  }

  async function loadDashboard() {
    if (!token) return;

    const payload = await apiFetch('/balance');
    account = payload.account;
    investments = payload.investments ?? [];
  }

  async function loadStatement() {
    if (!token) return;

    const query = new URLSearchParams({
      start_date: period.start_date,
      end_date: period.end_date,
    });

    const payload = await apiFetch(`/statement?${query.toString()}`);
    statement = payload.data ?? [];
  }

  async function handleLogin() {
    resetFeedback();
    isLoading = true;

    try {
      const payload = await apiFetch('/login', {
        method: 'POST',
        body: loginForm,
      });

      token = payload.token;
      user = payload.user;
      account = payload.account;
      localStorage.setItem(appStorageKey, token);
      await loadDashboard();
      await loadStatement();
      success = 'Login realizado com sucesso.';
    } catch (err) {
      error = err.message;
    } finally {
      isLoading = false;
    }
  }

  async function handleLogout() {
    resetFeedback();

    if (!token) {
      token = '';
      user = null;
      account = null;
      investments = [];
      statement = [];
      localStorage.removeItem(appStorageKey);
      return;
    }

    try {
      await apiFetch('/logout', { method: 'POST' });
    } catch (err) {
      error = err.message;
    } finally {
      token = '';
      user = null;
      account = null;
      investments = [];
      statement = [];
      localStorage.removeItem(appStorageKey);
    }
  }

  async function handlePix() {
    resetFeedback();
    if (!account || account.bloqueada) {
      error = 'Conta bloqueada. Não é possível realizar pix.';
      return;
    }

    try {
      isLoading = true;
      const payload = await apiFetch('/pix', {
        method: 'POST',
        body: {
          ...pixForm,
          conta_destino_id: Number(pixForm.conta_destino_id),
          valor: Number(pixForm.valor),
        },
      });

      account = payload.account;
      success = 'Pix enviado com sucesso.';
      pixForm = { conta_destino_id: '', valor: '', descricao: '' };
      await loadDashboard();
      await loadStatement();
    } catch (err) {
      error = err.message;
    } finally {
      isLoading = false;
    }
  }

  async function handleApplyInvestment() {
    resetFeedback();
    if (!account || account.bloqueada) {
      error = 'Conta bloqueada. Não é possível investir.';
      return;
    }

    try {
      isLoading = true;
      const payload = await apiFetch('/investments/apply', {
        method: 'POST',
        body: {
          tipo: applyForm.tipo,
          valor: Number(applyForm.valor),
        },
      });

      account = payload.account;
      success = 'Aplicação realizada com sucesso.';
      applyForm.valor = '';
      await loadDashboard();
      await loadStatement();
    } catch (err) {
      error = err.message;
    } finally {
      isLoading = false;
    }
  }

  async function handleRedeemInvestment() {
    resetFeedback();
    if (!account || account.bloqueada) {
      error = 'Conta bloqueada. Não é possível resgatar.';
      return;
    }

    try {
      isLoading = true;
      const payload = await apiFetch('/investments/redeem', {
        method: 'POST',
        body: {
          tipo: redeemForm.tipo,
          valor: Number(redeemForm.valor),
        },
      });

      account = payload.account;
      success = 'Resgate realizado com sucesso.';
      redeemForm.valor = '';
      await loadDashboard();
      await loadStatement();
    } catch (err) {
      error = err.message;
    } finally {
      isLoading = false;
    }
  }

  $effect(() => {
    if (token) {
      localStorage.setItem(appStorageKey, token);
    } else {
      localStorage.removeItem(appStorageKey);
    }
  });

  $effect(() => {
    if (token && !user) {
      loadMe();
    }
    if (!token) {
      isReady = true;
    }
  });

  onMount(() => {
    if (token) {
      loadMe();
    } else {
      isReady = true;
    }
  });

  const accountLabel = $derived(account ? account.bloqueada ? 'Conta bloqueada' : 'Conta ativa' : 'Sem conta');
  const availableBalance = $derived(account ? Number(account.saldo_disponivel ?? account.saldo) : 0);
</script>

<svelte:head>
  <title>GILbank • Cliente</title>
</svelte:head>

<div class="shell">
  {#if !token}
    <section class="auth-card">
      <div class="title-block">
        <p class="eyebrow">GILbank</p>
        <h1>Área do cliente</h1>
      </div>

      <form class="form-panel" onsubmit={(event) => { event.preventDefault(); handleLogin(); }}>
        <label>
          <span>E-mail</span>
          <input bind:value={loginForm.email} type="email" placeholder="cliente@gilbank.com" />
        </label>

        <label>
          <span>Senha</span>
          <input bind:value={loginForm.password} type="password" placeholder="Sua senha" />
        </label>

        {#if error}
          <div class="alert error">{error}</div>
        {/if}

        <button class="primary" type="submit" disabled={isLoading}>
          {isLoading ? 'Entrando...' : 'Entrar'}
        </button>
      </form>
    </section>
  {:else if !isReady}
    <section class="state-card">
      <p>Carregando sua conta...</p>
    </section>
  {:else}
    <header class="topbar">
      <div>
        <p class="eyebrow">GILbank</p>
        <h2>Olá, {user?.name ?? 'Cliente'}</h2>
      </div>
      <button class="secondary" onclick={() => handleLogout()}>Sair</button>
    </header>

    {#if error}
      <div class="alert error">{error}</div>
    {/if}

    {#if success}
      <div class="alert success">{success}</div>
    {/if}

    <main class="grid">
      <section class="panel summary">
        <div class="panel-header">
          <h3>Resumo da conta</h3>
          <span class:blocked={account?.bloqueada} class:active={!account?.bloqueada}>{accountLabel}</span>
        </div>

        <div class="balance-box">
          <small>Saldo disponível</small>
          <strong>{formatMoney(account?.saldo ?? 0)}</strong>
        </div>

        <div class="meta-grid">
          <div>
            <small>Limite</small>
            <p>{formatMoney(account?.limite ?? 0)}</p>
          </div>
          <div>
            <small>Saldo + limite</small>
            <p>{formatMoney(availableBalance)}</p>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h3>Aplicações</h3>
        </div>

        {#if investments.length}
          <div class="investment-list">
            {#each investments as item}
              <div class="investment-item">
                <span>{item.tipo_label}</span>
                <strong>{formatMoney(item.saldo_aplicado)}</strong>
              </div>
            {/each}
          </div>
        {:else}
          <p class="muted">Você ainda não possui aplicações.</p>
        {/if}
      </section>
    </main>

    <div class="two-col">
      <section class="panel">
        <div class="panel-header">
          <h3>Pix</h3>
        </div>

        <form class="stack-form" onsubmit={(event) => { event.preventDefault(); handlePix(); }}>
          <label>
            <span>Conta destino</span>
            <input bind:value={pixForm.conta_destino_id} type="number" min="1" placeholder="ID da conta" disabled={account?.bloqueada} />
          </label>

          <label>
            <span>Valor</span>
            <input bind:value={pixForm.valor} type="number" min="0.01" step="0.01" placeholder="0.00" disabled={account?.bloqueada} />
          </label>

          <label>
            <span>Descrição</span>
            <input bind:value={pixForm.descricao} type="text" placeholder="Mensagem ou descrição" disabled={account?.bloqueada} />
          </label>

          <button class="primary" type="submit" disabled={account?.bloqueada || isLoading}>Enviar pix</button>
        </form>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h3>Investimentos</h3>
        </div>

        <div class="stack-form">
          <label>
            <span>Tipo</span>
            <select bind:value={applyForm.tipo} disabled={account?.bloqueada}>
              <option value="cdb">CDB</option>
              <option value="cdi">CDI</option>
              <option value="poupanca">Poupança</option>
            </select>
          </label>

          <label>
            <span>Valor da aplicação</span>
            <input bind:value={applyForm.valor} type="number" step="0.01" min="0.01" placeholder="0.00" disabled={account?.bloqueada} />
          </label>

          <button class="primary" onclick={() => handleApplyInvestment()} disabled={account?.bloqueada || isLoading}>Aplicar</button>
        </div>

        <div class="stack-form spacer-top">
          <label>
            <span>Tipo</span>
            <select bind:value={redeemForm.tipo} disabled={account?.bloqueada}>
              <option value="cdb">CDB</option>
              <option value="cdi">CDI</option>
              <option value="poupanca">Poupança</option>
            </select>
          </label>

          <label>
            <span>Valor do resgate</span>
            <input bind:value={redeemForm.valor} type="number" step="0.01" min="0.01" placeholder="0.00" disabled={account?.bloqueada} />
          </label>

          <button class="secondary" onclick={() => handleRedeemInvestment()} disabled={account?.bloqueada || isLoading}>Resgatar</button>
        </div>
      </section>
    </div>

    <section class="panel statement-panel">
      <div class="panel-header">
        <h3>Extrato</h3>
      </div>

      <div class="filters">
        <label>
          <span>Data inicial</span>
          <input bind:value={period.start_date} type="date" />
        </label>
        <label>
          <span>Data final</span>
          <input bind:value={period.end_date} type="date" />
        </label>
        <button class="secondary" onclick={() => loadStatement()}>Filtrar</button>
      </div>

      {#if statement.length}
        <div class="statement-list">
          {#each statement as item}
            <div class="statement-item {item.is_entrada ? 'inbound' : 'outbound'}">
              <div>
                <strong>{item.tipo_label}</strong>
                <small>{item.descricao}</small>
              </div>
              <div class="statement-value">
                <span>{item.is_entrada ? '+' : '-'}{formatMoney(item.valor)}</span>
                <small>{new Date(item.created_at).toLocaleDateString('pt-BR')}</small>
              </div>
            </div>
          {/each}
        </div>
      {:else}
        <p class="muted">Nenhuma movimentação para o período selecionado.</p>
      {/if}
    </section>
  {/if}
</div>

<style>
  :global(body) {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #091a2a 0%, #0f3b64 100%);
    color: #f8fafc;
  }

  * {
    box-sizing: border-box;
  }

  .shell {
    max-width: 1100px;
    margin: 0 auto;
    padding: 32px 20px 60px;
  }

  .auth-card,
  .state-card,
  .panel {
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
  }

  .auth-card {
    max-width: 480px;
    margin: 80px auto;
    padding: 28px;
  }

  .state-card {
    padding: 40px 24px;
    text-align: center;
    margin-top: 80px;
  }

  .title-block h1,
  .topbar h2,
  .panel-header h3 {
    margin: 0;
  }

  .eyebrow {
    margin: 0 0 6px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 11px;
    color: #7dd3fc;
  }

  .form-panel,
  .stack-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 20px;
  }

  label {
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 0.92rem;
    color: #dfeaf7;
  }

  input,
  select,
  button {
    border-radius: 10px;
    border: 1px solid rgba(148, 163, 184, 0.32);
    padding: 12px 14px;
    background: rgba(15, 23, 42, 0.6);
    color: #f8fafc;
    font-size: 1rem;
  }

  input::placeholder {
    color: rgba(148, 163, 184, 0.8);
  }

  button {
    cursor: pointer;
    transition: transform 0.15s ease, opacity 0.2s ease;
  }

  button:hover {
    transform: translateY(-1px);
  }

  button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .primary {
    background: linear-gradient(135deg, #38bdf8, #2563eb);
    border: none;
  }

  .secondary {
    background: rgba(148, 163, 184, 0.12);
  }

  .topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
  }

  .grid,
  .two-col {
    display: grid;
    gap: 20px;
    margin-bottom: 20px;
  }

  .grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .two-col {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .panel {
    padding: 22px;
  }

  .panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
  }

  .panel-header span {
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
  }

  .panel-header .blocked {
    background: rgba(239, 68, 68, 0.15);
    color: #fca5a5;
  }

  .panel-header .active {
    background: rgba(34, 197, 94, 0.14);
    color: #86efac;
  }

  .balance-box {
    background: rgba(14, 116, 144, 0.18);
    border: 1px solid rgba(125, 211, 252, 0.25);
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 18px;
  }

  .balance-box small, .meta-grid small, .muted {
    color: #cbd5e1;
  }

  .balance-box strong {
    display: block;
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    margin-top: 8px;
  }

  .meta-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .meta-grid p {
    margin: 6px 0 0;
    font-weight: 600;
  }

  .investment-list,
  .statement-list {
    display: grid;
    gap: 12px;
  }

  .investment-item,
  .statement-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    background: rgba(15, 23, 42, 0.62);
    border: 1px solid rgba(148, 163, 184, 0.14);
    border-radius: 12px;
    padding: 12px 14px;
  }

  .statement-item {
    align-items: flex-start;
  }

  .statement-item strong,
  .investment-item strong {
    display: block;
  }

  .statement-item small,
  .investment-item span,
  .statement-value small {
    color: #cbd5e1;
  }

  .statement-item.inbound {
    border-color: rgba(34, 197, 94, 0.18);
  }

  .statement-item.outbound {
    border-color: rgba(248, 113, 113, 0.18);
  }

  .statement-value {
    text-align: right;
  }

  .statement-value span {
    display: block;
    font-weight: 700;
  }

  .filters {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    align-items: end;
    margin-bottom: 18px;
  }

  .alert {
    margin-top: 14px;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.92rem;
  }

  .alert.error {
    background: rgba(127, 29, 29, 0.35);
    color: #fecaca;
    border: 1px solid rgba(248, 113, 113, 0.25);
  }

  .alert.success {
    background: rgba(20, 83, 45, 0.35);
    color: #bbf7d0;
    border: 1px solid rgba(74, 222, 128, 0.25);
  }

  .spacer-top {
    margin-top: 18px;
  }

  @media (max-width: 760px) {
    .grid,
    .two-col,
    .filters {
      grid-template-columns: 1fr;
    }

    .topbar {
      flex-direction: column;
      align-items: flex-start;
    }
  }
</style>
