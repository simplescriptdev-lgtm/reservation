// Minimal client for drag & drop floor plan and reservations
const api = (action, opts={}) =>
  fetch(`/api.php?action=${action}`, {
    method: opts.method || (opts.body ? 'POST' : 'GET'),
    headers: {'Content-Type': 'application/json'},
    body: opts.body ? JSON.stringify(opts.body) : undefined
  }).then(r => r.json())

const $ = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));

const state = {
  tables: [],
  reservations: [],
  date: new Date().toISOString().slice(0,10),
  draggingId: null,
  offset: {x:0, y:0}
};

function init() {
  const d = $('#res-date');
  d.value = state.date;
  $('#res-date-label').textContent = state.date;
  d.addEventListener('change', () => {
    state.date = d.value;
    $('#res-date-label').textContent = state.date;
    loadReservations();
  });

  $('#add-table-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const body = Object.fromEntries(fd.entries());
    body.number = +body.number; body.seats = +body.seats;
    const res = await api('add_table', {body});
    if (res.ok) { await loadTables(); e.target.reset(); }
  });

  $('#save-layout').addEventListener('click', saveLayout);

  $('#add-res-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const body = Object.fromEntries(fd.entries());
    body.party_size = +body.party_size;
    body.table_id = +body.table_id;
    body.res_date = state.date;
    const res = await api('add_reservation', {body});
    if (res.ok) { e.target.reset(); loadReservations(); }
  });

  loadTables().then(loadReservations);
}

async function loadTables() {
  const rows = await api('list_tables');
  state.tables = rows;
  $('#table-count').textContent = rows.length;
  renderTableList();
  renderCanvas();
  renderReservationTableSelect();
}

async function saveLayout() {
  // Persist positions for all tables
  await Promise.all(state.tables.map(t => api('update_table_pos', {body:{id:t.id, x:t.x, y:t.y}})));
  alert('Розміщення збережено');
}

async function loadReservations() {
  const rows = await api('list_reservations&date=' + state.date);
  state.reservations = rows;
  renderReservations();
}

function renderTableList() {
  const box = $('#table-list');
  box.innerHTML = '';
  state.tables.forEach(t => {
    const item = document.createElement('div');
    item.className = 'row';
    item.style.display = 'flex';
    item.style.justifyContent = 'space-between';
    item.style.alignItems = 'center';
    item.style.padding = '6px 4px';
    item.innerHTML = `<span>#${t.number} <span class="table-chip">${t.seats} місць</span></span>
      <button class="btn" data-del="${t.id}">×</button>`;
    item.querySelector('button').addEventListener('click', async () => {
      if (confirm('Видалити стіл #' + t.number + '?')) {
        await api('delete_table&id=' + t.id);
        loadTables();
      }
    });
    box.appendChild(item);
  });
}

function renderReservationTableSelect() {
  const sel = $('#res-table-select');
  sel.innerHTML = state.tables.map(t => `<option value="${t.id}">#${t.number} (${t.seats})</option>`).join('');
}

function renderCanvas() {
  const svg = $('#floor-canvas');
  while (svg.firstChild) svg.removeChild(svg.firstChild);

  state.tables.forEach(t => {
    const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
    g.setAttribute('data-id', t.id);
    g.style.cursor = 'move';

    let shape;
    if (t.shape === 'rect') {
      shape = document.createElementNS(svg.namespaceURI, 'rect');
      shape.setAttribute('width', t.width);
      shape.setAttribute('height', t.height);
      shape.setAttribute('rx', 10);
    } else {
      shape = document.createElementNS(svg.namespaceURI, 'circle');
      shape.setAttribute('r', Math.round((t.width || 60)/2));
    }
    shape.setAttribute('fill', '#1e2430');
    shape.setAttribute('stroke', '#2d6cdf');
    shape.setAttribute('stroke-width', 2);

    const label = document.createElementNS(svg.namespaceURI, 'text');
    label.setAttribute('fill', '#dbeafe');
    label.setAttribute('text-anchor', 'middle');
    label.setAttribute('dominant-baseline', 'middle');
    label.setAttribute('font-size', '14');
    label.textContent = t.number;

    // Position
    const x = t.x || 60, y = t.y || 60;
    if (t.shape === 'rect') {
      shape.setAttribute('x', x - (t.width/2));
      shape.setAttribute('y', y - (t.height/2));
    } else {
      shape.setAttribute('cx', x);
      shape.setAttribute('cy', y);
    }
    label.setAttribute('x', x);
    label.setAttribute('y', y);

    // Drag logic
    g.addEventListener('mousedown', (ev) => {
      state.draggingId = t.id;
      state.offset = {x: ev.offsetX - x, y: ev.offsetY - y};
    });
    svg.addEventListener('mousemove', (ev) => {
      if (state.draggingId === t.id) {
        const nx = ev.offsetX - state.offset.x;
        const ny = ev.offsetY - state.offset.y;
        t.x = Math.max(30, Math.min(870, nx));
        t.y = Math.max(30, Math.min(490, ny));
        if (t.shape === 'rect') {
          shape.setAttribute('x', t.x - (t.width/2));
          shape.setAttribute('y', t.y - (t.height/2));
        } else {
          shape.setAttribute('cx', t.x);
          shape.setAttribute('cy', t.y);
        }
        label.setAttribute('x', t.x);
        label.setAttribute('y', t.y);
      }
    });
    window.addEventListener('mouseup', () => state.draggingId = null);

    g.appendChild(shape);
    g.appendChild(label);
    svg.appendChild(g);
  });
}

function renderReservations() {
  const tb = $('#res-tbody');
  tb.innerHTML = '';
  state.reservations.forEach(r => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${r.res_time.slice(0,5)}</td>
      <td>${escapeHtml(r.guest_lastname)}</td>
      <td>${r.party_size}</td>
      <td>#${r.table_number}</td>
      <td>${escapeHtml(r.notes || '')}</td>
      <td><button class="btn" data-del="${r.id}">×</button></td>
    `;
    tr.querySelector('button').addEventListener('click', async () => {
      if (confirm('Видалити резервацію?')) {
        await api('delete_reservation&id=' + r.id);
        loadReservations();
      }
    });
    tb.appendChild(tr);
  });
}

function escapeHtml(s){ return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])) }

document.addEventListener('DOMContentLoaded', init);
