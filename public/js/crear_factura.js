(function () {
  const body = document.getElementById('detalle-body');
  const addBtn = document.getElementById('agregar-detalle');
  const rowTpl = document.getElementById('row-template');

  const subtotalVisible = document.getElementById('subtotal_visible');
  const descuentoInp = document.getElementById('descuento');         // ahora % por defecto
  const ivaPctInp = document.getElementById('iva_porcentaje');
  const ivaVisible = document.getElementById('iva_visible');
  const totalHidden = document.getElementById('total');
  const totalVisible = document.getElementById('total_visible');
  const ivaHidden = document.getElementById('iva');
  const subtotalHidden = document.getElementById('subtotal_backend');

  const form = document.getElementById('form-factura');

  const CRC = new Intl.NumberFormat('es-CR', {
    style: 'currency', currency: 'CRC',
    maximumFractionDigits: 2, minimumFractionDigits: 2
  });

  const parseNum = (v) => {
    const n = Number.parseFloat((v ?? '').toString().replace(',', '.'));
    return Number.isFinite(n) ? n : 0;
  };

  function setPrecioFromOption(row) {
    const prodSel = row.querySelector('.producto');
    const servSel = row.querySelector('.servicio');
    const precioInp = row.querySelector('.precio');

    if (prodSel.value) {
      servSel.value = '';
      const p = parseNum(prodSel.selectedOptions[0]?.dataset?.precio);
      if (p > 0) precioInp.value = p.toFixed(2);
    } else if (servSel.value) {
      prodSel.value = '';
      const p = parseNum(servSel.selectedOptions[0]?.dataset?.precio);
      if (p > 0) precioInp.value = p.toFixed(2);
    }
  }

  function calcRow(row) {
    const cant = parseNum(row.querySelector('.cantidad').value);
    const precio = parseNum(row.querySelector('.precio').value);
    const subtotalInp = row.querySelector('.subtotal');
    const subtotal = cant * precio;
    subtotalInp.value = subtotal.toFixed(2);
  }

  function subtotalFromRows() {
    let s = 0;
    body.querySelectorAll('.detalle-row').forEach(r => {
      s += parseNum(r.querySelector('.subtotal').value);
    });
    return s;
  }

  // === Cálculo principal: DESCUENTO COMO PORCENTAJE ===
  function recalcTotals() {
    // 1) Subtotal de filas
    const subtotal = subtotalFromRows();
    subtotalHidden.value = subtotal.toFixed(2);
    subtotalVisible.value = CRC.format(subtotal);

    // 2) Descuento (%) -> clamp 0..100
    let descPct = parseNum(descuentoInp.value);
    if (!Number.isFinite(descPct) || descPct < 0) descPct = 0;
    if (descPct > 100) descPct = 100;
    descuentoInp.value = descPct.toFixed(2); // normaliza la caja

    // Monto de descuento
    const descMonto = +(subtotal * (descPct / 100)).toFixed(2);

    // 3) Base imponible
    const base = Math.max(0, subtotal - descMonto);

    // 4) IVA
    const pct = Math.max(0, parseNum(ivaPctInp.value));
    const iva = +(base * (pct / 100)).toFixed(2);

    // 5) Total
    const total = +(base + iva).toFixed(2);

    // 6) Pintar y setear hidden
    ivaHidden.value = iva.toFixed(2);
    ivaVisible.value = CRC.format(iva);
    totalHidden.value = total.toFixed(2);
    totalVisible.value = CRC.format(total);
  }

  function recalcAll() {
    body.querySelectorAll('.detalle-row').forEach(calcRow);
    recalcTotals();
  }

  // Delegación de eventos
  body.addEventListener('change', (e) => {
    const row = e.target.closest('.detalle-row');
    if (!row) return;
    if (e.target.classList.contains('producto') || e.target.classList.contains('servicio')) {
      setPrecioFromOption(row);
      calcRow(row);
      recalcTotals();
    }
    if (e.target.classList.contains('cantidad') || e.target.classList.contains('precio')) {
      calcRow(row);
      recalcTotals();
    }
  });

  body.addEventListener('input', (e) => {
    const row = e.target.closest('.detalle-row');
    if (!row) return;
    if (e.target.classList.contains('cantidad') || e.target.classList.contains('precio')) {
      calcRow(row);
      recalcTotals();
    }
  });

  body.addEventListener('click', (e) => {
    if (e.target.classList.contains('btn-eliminar')) {
      const rows = body.querySelectorAll('.detalle-row');
      if (rows.length > 1) {
        e.target.closest('.detalle-row').remove();
      } else {
        const row = rows[0];
        row.querySelector('.producto').value = '';
        row.querySelector('.servicio').value = '';
        row.querySelector('.cantidad').value = '';
        row.querySelector('.precio').value = '';
        row.querySelector('.subtotal').value = '0.00';
      }
      recalcTotals();
    }
  });

  addBtn.addEventListener('click', () => {
    const frag = rowTpl.content.cloneNode(true);
    body.appendChild(frag);
    recalcTotals(); // recalcula al agregar fila
  });

  // Recalcular cuando cambie descuento (%) o (si se habilita) el % de IVA
  descuentoInp.addEventListener('input', recalcTotals);
  ivaPctInp.addEventListener('input', recalcTotals);

  // Validación previa al submit
  form.addEventListener('submit', (e) => {
    let valid = true;
    const rows = body.querySelectorAll('.detalle-row');
    if (rows.length === 0) valid = false;

    rows.forEach((row) => {
      const prod = row.querySelector('.producto').value;
      const serv = row.querySelector('.servicio').value;
      const cant = parseNum(row.querySelector('.cantidad').value);
      const precio = parseNum(row.querySelector('.precio').value);
      const unoElegido = (!!prod) ^ (!!serv);
      if (!unoElegido || cant <= 0 || precio <= 0) {
        valid = false;
        row.classList.add('row-error');
      } else {
        row.classList.remove('row-error');
      }
    });

    if (!valid) {
      e.preventDefault();
      alert('Revise los detalles: producto o servicio (no ambos), cantidad > 0 y precio > 0.');
      return;
    }

    // Asegurar números finales
    recalcAll();
  });

  // Inicial: asegura % válido y calcula
  if (!descuentoInp.hasAttribute('max')) descuentoInp.setAttribute('max', '100');
  if (!descuentoInp.hasAttribute('min')) descuentoInp.setAttribute('min', '0');
  recalcAll();
})();
