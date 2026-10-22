/* Lumiora Live Event Studio — Fetch API CRUD without page reload. */
'use strict';

const apiUrl = 'event-api.php';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const byId = (id) => document.getElementById(id);
const ui = {
  rows: byId('event-rows'), results: byId('result-count'), pages: byId('page-buttons'),
  state: byId('request-state'), region: byId('table-region'), alert: byId('alert'),
  filters: byId('filter-form'), dialog: byId('event-dialog'), form: byId('event-form'),
  formError: byId('form-error'), save: byId('save-event')
};
const state = { page: 1, data: [], controller: null, requestId: 0, busy: false };
let searchTimer = null;

// Centralized AJAX handler: parse JSON on both success and failure.
async function apiRequest(method, payload = null, params = null, signal = undefined) {
  const url = params ? `${apiUrl}?${new URLSearchParams(params)}` : apiUrl;
  const options = { method, credentials: 'same-origin', headers: { Accept: 'application/json' }, signal };
  if (payload !== null) {
    options.headers['Content-Type'] = 'application/json';
    options.headers['X-CSRF-Token'] = csrfToken;
    options.body = JSON.stringify(payload);
  }
  let response;
  try { response = await fetch(url, options); }
  catch (error) {
    if (error.name === 'AbortError') throw error;
    throw new Error('Network error: check XAMPP Apache and your connection.');
  }
  const body = await response.text();
  let json;
  try { json = JSON.parse(body); }
  catch { throw new Error('Server did not return JSON. Check PHP errors or sign in again.'); }
  if (!response.ok || json.ok !== true) {
    throw new Error(json.message || `Request failed (${response.status}).`);
  }
  return json;
}

function showAlert(message, type = 'success') {
  ui.alert.textContent = message;
  ui.alert.className = `notice ${type}`;
  ui.alert.hidden = false;
}
function setRequestState(message, type = '') {
  ui.state.textContent = message;
  ui.state.className = `connection ${type}`.trim();
}
function el(tag, className = '', content = '') {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (content !== '') node.textContent = String(content);
  return node;
}
function tableCell(content, cls = '') {
  return el('td', cls, content);
}
function actionButton(label, cls, callback) {
  const button = el('button', cls, label);
  button.type = 'button';
  button.addEventListener('click', callback);
  return button;
}
function pill(label, className) {
  return el('span', `status-pill ${className}`, label);
}

function renderEvents(events) {
  state.data = events;
  ui.rows.replaceChildren();
  if (!events.length) {
    const row = el('tr');
    const cell = tableCell('No matching events. Try another search or choose Add New Event.', 'empty');
    cell.colSpan = 7;
    row.appendChild(cell);
    ui.rows.appendChild(row);
    return;
  }
  for (const event of events) {
    const row = el('tr');
    const posterCell = el('td');
    if (event.poster_path) {
      const image = el('img', 'poster');
      image.alt = `Poster for ${event.event_name}`;
      image.src = event.poster_path;
      image.loading = 'lazy';
      image.addEventListener('error', () => {
        posterCell.replaceChildren(el('span', 'poster-placeholder', 'No poster'));
      });
      posterCell.appendChild(image);
    } else {
      posterCell.appendChild(el('span', 'poster-placeholder', 'No poster'));
    }
    row.appendChild(posterCell);
    const details = tableCell('', 'event-name');
    details.appendChild(el('strong', '', event.event_name));
    details.appendChild(el('small', '', event.venue));
    row.appendChild(details);
    row.appendChild(tableCell(event.category));
    row.appendChild(tableCell(event.event_date));
    row.appendChild(tableCell(event.registrations));
    const statusCell = el('td');
    statusCell.appendChild(pill(event.event_status, event.event_status));
    if (event.is_archived) statusCell.appendChild(pill('archived', 'archived'));
    row.appendChild(statusCell);
    const actionsCell = el('td');
    const actions = el('div', 'action-buttons');
    if (event.is_archived) {
      actions.appendChild(actionButton('Restore', 'mini', () => performAction('restore', event)));
    } else {
      actions.appendChild(actionButton('Edit', 'mini', () => openEventDialog(event)));
      actions.appendChild(actionButton('Delete', 'danger', () => performAction('delete', event)));
      actions.appendChild(actionButton(event.event_status === 'open' ? 'Close' : 'Open', 'mini',
        () => performAction('status', event)));
    }
    actionsCell.appendChild(actions);
    row.appendChild(actionsCell);
    ui.rows.appendChild(row);
  }
}
function renderPagination(pagination) {
  ui.pages.replaceChildren();
  const button = (label, number, active = false) => {
    const elButton = actionButton(label, `page ${active ? 'current' : ''}`, () => {
      state.page = number;
      loadEvents();
    });
    if (active) elButton.setAttribute('aria-current', 'page');
    if (active) elButton.disabled = true;
    return elButton;
  };
  if (pagination.page > 1) ui.pages.appendChild(button('← Previous', pagination.page - 1));
  for (let i = 1; i <= pagination.pages; i++) {
    if (pagination.pages <= 8 || Math.abs(i - pagination.page) <= 2 || i === 1 || i === pagination.pages) {
      ui.pages.appendChild(button(String(i), i, i === pagination.page));
    } else if (ui.pages.lastChild?.textContent !== '…') {
      ui.pages.appendChild(el('span', '', '…'));
    }
  }
  if (pagination.page < pagination.pages) ui.pages.appendChild(button('Next →', pagination.page + 1));
  ui.results.textContent = `${pagination.total} matching event(s) · Page ${pagination.page} of ${pagination.pages}`;
}
function renderSummary(counts) {
  byId('stat-total').textContent = counts.total;
  byId('stat-active').textContent = counts.active;
  byId('stat-open').textContent = counts.open;
  byId('stat-archived').textContent = counts.archived;
}

// Aborting older requests prevents delayed search results replacing newer ones.
async function loadEvents() {
  if (state.controller) state.controller.abort();
  const controller = new AbortController();
  const requestId = ++state.requestId;
  state.controller = controller;
  setRequestState('Loading events…', 'busy');
  ui.region.setAttribute('aria-busy', 'true');
  const params = {
    search: byId('search').value.trim(), category: byId('category').value,
    status: byId('status').value, archive: byId('archive').value, page: String(state.page)
  };
  try {
    const json = await apiRequest('GET', null, params, controller.signal);
    if (requestId !== state.requestId) return;
    state.page = json.pagination.page;
    renderEvents(json.data);
    renderPagination(json.pagination);
    renderSummary(json.counts);
    setRequestState('Live · Connected');
  } catch (error) {
    if (error.name === 'AbortError' || requestId !== state.requestId) return;
    setRequestState('Connection error', 'error');
    ui.rows.replaceChildren();
    const row = el('tr');
    const cell = tableCell(error.message, 'empty');
    cell.colSpan = 7; row.appendChild(cell); ui.rows.appendChild(row);
    ui.results.textContent = 'Unable to load events';
    ui.pages.replaceChildren();
    showAlert(error.message, 'error');
  } finally {
    if (requestId === state.requestId) ui.region.setAttribute('aria-busy', 'false');
  }
}

function openEventDialog(event = null) {
  ui.form.reset();
  ui.formError.hidden = true;
  byId('edit-id').value = event ? String(event.event_id) : '';
  byId('dialog-title').textContent = event ? `Edit Event #${event.event_id}` : 'Add New Event';
  ui.save.textContent = event ? 'Save Changes' : 'Add Event';
  if (event) {
    for (const name of ['event_name','category','event_date','venue','description','event_status']) {
      ui.form.elements.namedItem(name).value = event[name] ?? '';
    }
  }
  ui.dialog.showModal();
  byId('event_name').focus();
}

async function saveEvent(event) {
  event.preventDefault();
  ui.formError.hidden = true;
  if (!ui.form.reportValidity()) return;
  const values = Object.fromEntries(new FormData(ui.form));
  const editId = values.event_id;
  delete values.event_id;
  if (editId) values.event_id = Number(editId);
  ui.save.disabled = true;
  ui.save.textContent = 'Saving…';
  try {
    const response = await apiRequest(editId ? 'PATCH' : 'POST', values);
    ui.dialog.close();
    state.page = 1;
    await loadEvents();
    showAlert(response.message);
  } catch (error) {
    ui.formError.textContent = error.message;
    ui.formError.hidden = false;
  } finally {
    ui.save.disabled = false;
    ui.save.textContent = editId ? 'Save Changes' : 'Add Event';
  }
}

async function performAction(action, event) {
  if (state.busy) return;
  const archive = action === 'delete';
  const restore = action === 'restore';
  const nextStatus = event.event_status === 'open' ? 'closed' : 'open';
  const prompt = archive
    ? `Archive "${event.event_name}"? Its registrations will not be deleted.`
    : restore
      ? `Restore "${event.event_name}"?`
      : `Change "${event.event_name}" registration status to ${nextStatus.toUpperCase()}?`;
  if (!window.confirm(prompt)) return;
  const method = archive ? 'DELETE' : 'POST';
  const body = archive ? { event_id: event.event_id }
    : restore ? { action: 'restore', event_id: event.event_id }
      : { action: 'change_status', event_id: event.event_id, event_status: nextStatus };
  state.busy = true;
  setRequestState('Updating event…', 'busy');
  try {
    const result = await apiRequest(method, body);
    await loadEvents();
    showAlert(result.message);
  } catch (error) {
    setRequestState('Action failed', 'error');
    showAlert(error.message, 'error');
  } finally {
    state.busy = false;
  }
}

// Event handlers: no full-page navigation for CRUD operations.
byId('add-event').addEventListener('click', () => openEventDialog());
byId('reload-events').addEventListener('click', () => loadEvents());
ui.form.addEventListener('submit', saveEvent);
ui.dialog.querySelectorAll('.close-dialog').forEach(button => button.addEventListener('click', () => ui.dialog.close()));
ui.filters.addEventListener('submit', (event) => { event.preventDefault(); state.page = 1; loadEvents(); });
byId('search').addEventListener('input', () => {
  window.clearTimeout(searchTimer);
  searchTimer = window.setTimeout(() => { state.page = 1; loadEvents(); }, 350);
});
for (const name of ['category','status','archive']) {
  byId(name).addEventListener('change', () => { state.page = 1; loadEvents(); });
}
ui.filters.addEventListener('reset', () => {
  window.clearTimeout(searchTimer);
  window.setTimeout(() => { state.page = 1; loadEvents(); }, 0);
});
loadEvents();
