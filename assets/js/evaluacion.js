(function () {
  "use strict";

  const dataEl = document.getElementById("eval-data");
  if (!dataEl) return;

  const { controles, recomendaciones, grupos, nivelesMadurez, opcionesRespuesta, strings } = JSON.parse(dataEl.textContent);
  const form = document.getElementById("form-evaluacion");
  const dashboard = document.getElementById("eval-dashboard");

  const DIMENSIONES = ["C", "I", "D"];

  let chartBarras = null;
  let chartCircular = null;
  let chartMadurezPreguntas = null;
  let ultimoResultado = null;

  function semaforo(pct) {
    if (pct >= 80) return { color: "var(--risk-low)", nivel: strings.verde, texto: strings.verdeTexto };
    if (pct >= 60) return { color: "var(--risk-mid)", nivel: strings.amarillo, texto: strings.amarilloTexto };
    return { color: "var(--risk-crit)", nivel: strings.rojo, texto: strings.rojoTexto };
  }

  function nivelMadurez(pctControl) {
    if (pctControl <= 0)   return 0;
    if (pctControl <= 20)  return 1;
    if (pctControl <= 45)  return 2;
    if (pctControl <= 70)  return 3;
    if (pctControl <= 90)  return 4;
    return 5;
  }

  function colorPorNivelManual(n) {
    if (n <= 1) return "var(--risk-crit)";
    if (n === 2) return "var(--risk-high)";
    if (n === 3) return "var(--risk-mid)";
    return "var(--risk-low)";
  }

  function resolverColorCss(valor) {
    return valor.startsWith("var")
      ? getComputedStyle(document.documentElement).getPropertyValue(valor.match(/--[\w-]+/)[0]).trim()
      : valor;
  }

  // ===== Nivel de madurez manual (botones 0-5) + comentario por pregunta =====
  // El botón de comentario está siempre habilitado en todas las preguntas
  // (el comentario es opcional en general y se guarda en BD si se escribe).
  // Si la respuesta Sí/No/No aplica es "na", el nivel se fuerza a 0 y se
  // deshabilitan los botones. Cuando la respuesta es "na" o el nivel es 0,
  // el cuadro de comentario se revela automáticamente y pasa a ser obligatorio.
  function actualizarBotonesNivel(pid, valorSeleccionado, deshabilitado) {
    const botones = form.querySelectorAll(`.eval-nivel-btn[data-nivel-btn="${pid}"]`);
    botones.forEach((b) => {
      b.classList.toggle("active", Number(b.dataset.valor) === valorSeleccionado);
      b.disabled = deshabilitado;
    });
  }

  function actualizarEstadoComentario(pid) {
    const checked = form.querySelector(`input[name="p${pid}"]:checked`);
    const nivelInput = document.getElementById(`nivel${pid}`);
    const btn = document.getElementById(`btnComentario${pid}`);
    const caja = document.getElementById(`comentario${pid}`);
    if (!nivelInput || !btn || !caja) return;

    const esNA = !!checked && checked.value === "na";
    if (esNA) {
      nivelInput.value = "0";
    }
    actualizarBotonesNivel(pid, nivelInput.value === "" ? null : Number(nivelInput.value), esNA);

    const nivelActual = nivelInput.value === "" ? null : Number(nivelInput.value);
    const requiereComentario = esNA || nivelActual === 0;

    caja.required = requiereComentario;
    if (requiereComentario) {
      caja.classList.remove("d-none");
      btn.classList.add("active");
    }
  }

  function inicializarNivelesManual() {
    // Delegación de eventos: un solo listener en el <form> en vez de uno por
    // botón. Así no importa cuándo se creó cada botón ni si algo del navegador
    // (extensiones, etc.) interfiere con listeners puestos elemento por elemento.
    form.addEventListener("click", (e) => {
      const boton = e.target.closest(".eval-nivel-btn");
      if (boton) {
        if (boton.disabled) return;
        const pid = boton.dataset.nivelBtn;
        const nivelInput = document.getElementById(`nivel${pid}`);
        if (!nivelInput) return;
        nivelInput.value = boton.dataset.valor;
        actualizarEstadoComentario(pid);
        return;
      }

      const btnComentario = e.target.closest(".eval-comentario-btn");
      if (btnComentario) {
        const caja = document.getElementById(btnComentario.dataset.target);
        if (!caja) return;
        const abierto = !caja.classList.toggle("d-none");
        btnComentario.classList.toggle("active", abierto);
        if (abierto) caja.focus();
      }
    });

    form.addEventListener("change", (e) => {
      const match = e.target.matches('input[type="radio"]') && e.target.name.match(/^p(\d+)$/);
      if (match) actualizarEstadoComentario(match[1]);
    });

    controles.forEach((c) => {
      c.preguntas.forEach((p) => {
        actualizarEstadoComentario(p.id);
      });
    });
  }

  function calcular(respuestas) {
    // respuestas: { [preguntaId]: "si" | "no" | "na" }
    const obtenido = { C: 0, I: 0, D: 0 };
    const maximo   = { C: 0, I: 0, D: 0 };
    const resultadosControl = [];

    controles.forEach((c) => {
      let aplicables = 0;
      let cumplidas  = 0;

      c.preguntas.forEach((p) => {
        const r = respuestas[p.id];
        if (r === "na") return;
        aplicables++;
        if (r === "si") cumplidas++;
      });

      const pctControl = aplicables > 0 ? (cumplidas / aplicables) * 100 : 0;
      const madurez = nivelMadurez(pctControl);

      resultadosControl.push({
        id: c.id, codigo: c.codigo, nombre: c.nombre, grupo: c.grupo,
        pctControl: Math.round(pctControl), madurez,
      });

      DIMENSIONES.forEach((dim) => {
        const pesoDim = { C: c.peso_c, I: c.peso_i, D: c.peso_d }[dim];
        const factorPeso = c.peso * pesoDim;
        obtenido[dim] += (madurez / 5) * factorPeso;
        maximo[dim]   += factorPeso;
      });
    });

    const pct = {};
    DIMENSIONES.forEach((dim) => {
      pct[dim] = maximo[dim] > 0 ? Math.round((obtenido[dim] / maximo[dim]) * 100) : 0;
    });

    const global = Math.round((pct.C + pct.I + pct.D) / 3);

    return { pct, global, resultadosControl };
  }

  function renderDimensionCards(pct) {
    const container = document.getElementById("eval-dimension-cards");
    container.innerHTML = "";

    DIMENSIONES.forEach((dim) => {
      const s = semaforo(pct[dim]);
      const col = document.createElement("div");
      col.className = "col-md-4";
      col.innerHTML = `
        <div class="eval-dim-card" style="--dim-color:${s.color}">
          <span class="eval-dim-tag">${grupos[dim]}</span>
          <div class="eval-dim-pct">${pct[dim]}%</div>
          <span class="eval-badge" style="background:${s.color}">${s.nivel}</span>
          <p class="eval-dim-interp">${s.texto}</p>
        </div>`;
      container.appendChild(col);
    });
  }

  function renderGlobal(global) {
    const s = semaforo(global);
    document.getElementById("eval-global-pct").textContent = global + "%";
    document.getElementById("eval-global-pct").style.color = s.color;
    const badge = document.getElementById("eval-global-badge");
    badge.textContent = s.nivel;
    badge.style.background = s.color;
    document.getElementById("eval-global-text").textContent = s.texto;
  }

  function renderCharts(pct) {
    const ctxBarras = document.getElementById("chart-barras").getContext("2d");
    const ctxCircular = document.getElementById("chart-circular").getContext("2d");

    const colores = DIMENSIONES.map((d) => resolverColorCss(semaforo(pct[d]).color));

    if (chartBarras) chartBarras.destroy();
    if (chartCircular) chartCircular.destroy();

    chartBarras = new Chart(ctxBarras, {
      type: "bar",
      data: {
        labels: DIMENSIONES.map((d) => grupos[d]),
        datasets: [{
          label: strings.pctCumplimiento,
          data: DIMENSIONES.map((d) => pct[d]),
          backgroundColor: colores,
          borderRadius: 6,
        }],
      },
      options: {
        scales: {
          y: { beginAtZero: true, max: 100, ticks: { color: "#9AA7C2" }, grid: { color: "#23314B" } },
          x: { ticks: { color: "#9AA7C2" }, grid: { display: false } },
        },
        plugins: { legend: { display: false } },
      },
    });

    const globalPct = Math.round((pct.C + pct.I + pct.D) / 3);
    chartCircular = new Chart(ctxCircular, {
      type: "doughnut",
      data: {
        labels: [strings.cumplimiento, strings.brecha],
        datasets: [{
          data: [globalPct, 100 - globalPct],
          backgroundColor: [resolverColorCss("var(--risk-" + (globalPct >= 80 ? "low" : globalPct >= 60 ? "mid" : "crit") + ")"), "#1B2740"],
          borderWidth: 0,
        }],
      },
      options: {
        cutout: "70%",
        plugins: { legend: { labels: { color: "#9AA7C2" } } },
      },
    });
  }

  function renderChartMadurezPreguntas(nivelesPorPregunta) {
    const canvas = document.getElementById("chart-madurez-preguntas");
    if (!canvas) return;

    const preguntas = [];
    controles.forEach((c) => {
      c.preguntas.forEach((p) => preguntas.push(p));
    });

    const wrap = canvas.closest(".eval-chart-preguntas-wrap");
    if (wrap) wrap.style.height = Math.max(320, preguntas.length * 24) + "px";

    const niveles = preguntas.map((p) => nivelesPorPregunta[p.id] ?? 0);
    const colores = niveles.map((n) => resolverColorCss(colorPorNivelManual(n)));

    if (chartMadurezPreguntas) chartMadurezPreguntas.destroy();

    chartMadurezPreguntas = new Chart(canvas.getContext("2d"), {
      type: "bar",
      data: {
        labels: preguntas.map((p) => `${p.id}. ${p.texto}`),
        datasets: [{
          label: strings.nivelMadurezChart,
          data: niveles,
          backgroundColor: colores,
          borderRadius: 4,
        }],
      },
      options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { beginAtZero: true, max: 5, ticks: { color: "#9AA7C2", stepSize: 1 }, grid: { color: "#23314B" } },
          y: { ticks: { color: "#9AA7C2", autoSkip: false }, grid: { display: false } },
        },
        plugins: { legend: { display: false } },
      },
    });
  }

  function renderRecomendaciones(pct) {
    const lista = document.getElementById("eval-recos-list");
    lista.innerHTML = "";

    // Dimensiones con brecha relevante (por debajo de 80%), de peor a mejor
    const debiles = DIMENSIONES
      .filter((d) => pct[d] < 80)
      .sort((a, b) => pct[a] - pct[b]);

    if (debiles.length === 0) {
      lista.innerHTML = `<li>${strings.sinBrechas}</li>`;
      return;
    }

    debiles.forEach((d) => {
      const li = document.createElement("li");
      li.innerHTML = `<strong>${grupos[d]} (${pct[d]}%):</strong> ${recomendaciones[d]}`;
      lista.appendChild(li);
    });
  }

  function renderControlesDebiles(resultadosControl) {
    const lista = document.getElementById("eval-weak-list");
    lista.innerHTML = "";

    const debiles = resultadosControl
      .filter((r) => r.madurez < 4)
      .sort((a, b) => a.madurez - b.madurez)
      .slice(0, 8);

    if (debiles.length === 0) {
      lista.innerHTML = `<li>${strings.madurezOk}</li>`;
      return;
    }

    debiles.forEach((r) => {
      const li = document.createElement("li");
      li.innerHTML = `<span class="eval-weak-estado estado-${r.madurez}">${strings.madurez} ${r.madurez} — ${nivelesMadurez[r.madurez]}</span> ${r.codigo} ${r.nombre}`;
      lista.appendChild(li);
    });
  }

  async function guardarEvaluacion(organizacion, evaluador, fecha, respuestas) {
    const statusEl = document.getElementById("eval-guardar-status");
    if (statusEl) {
      statusEl.textContent = strings.guardando;
      statusEl.style.color = "var(--text-muted)";
    }
    try {
      const res = await fetch("api/guardar-evaluacion.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ organizacion, evaluador, fecha, respuestas }),
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.error || "Error desconocido al guardar.");
      if (statusEl) {
        statusEl.textContent = strings.guardado;
        statusEl.style.color = "var(--risk-low)";
      }
    } catch (err) {
      console.error("Error al guardar evaluación:", err);
      if (statusEl) {
        statusEl.textContent = strings.errorGuardar;
        statusEl.style.color = "var(--risk-crit)";
      }
    }
  }

  // ===== Exportación de resultados (PDF / Excel) =====
  function nombreArchivo(ext) {
    const org = (ultimoResultado.organizacion || "evaluacion").replace(/[^a-z0-9]+/gi, "_");
    return `RiskGuard_${org}_${ultimoResultado.fecha}.${ext}`;
  }

  function filasPreguntas() {
    const filas = [];
    controles.forEach((c) => {
      c.preguntas.forEach((p) => {
        const r = ultimoResultado.respuestas[p.id];
        filas.push([
          p.id,
          c.nombre,
          p.texto,
          opcionesRespuesta[r.respuesta] || r.respuesta,
          r.nivel_madurez,
          r.comentario || "",
        ]);
      });
    });
    return filas;
  }

  function exportarPDF() {
    if (!ultimoResultado) return;
    if (typeof window.jspdf === "undefined") {
      alert("jsPDF no se cargó (revisa la conexión a internet).");
      return;
    }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: "pt", format: "a4" });
    const margin = 40;
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    let y = margin;

    doc.setFontSize(16);
    doc.text(`RiskGuard — ${strings.exportTitulo}`, margin, y); y += 22;

    doc.setFontSize(10);
    doc.text(`${strings.exportOrganizacion}: ${ultimoResultado.organizacion}`, margin, y); y += 14;
    doc.text(`${strings.exportEvaluador}: ${ultimoResultado.evaluador}`, margin, y); y += 14;
    doc.text(`${strings.exportFecha}: ${ultimoResultado.fecha}`, margin, y); y += 20;

    doc.setFontSize(12);
    doc.text(`${strings.exportGlobal}: ${ultimoResultado.global}%`, margin, y); y += 16;

    doc.autoTable({
      startY: y,
      head: [[strings.exportDimension, strings.pctCumplimiento]],
      body: DIMENSIONES.map((d) => [grupos[d], ultimoResultado.pct[d] + "%"]),
      margin: { left: margin, right: margin },
      styles: { fontSize: 9 },
    });
    y = doc.lastAutoTable.finalY + 20;

    ["chart-barras", "chart-circular", "chart-madurez-preguntas"].forEach((id) => {
      const canvas = document.getElementById(id);
      if (!canvas || !canvas.width || !canvas.height) return;
      const imgData = canvas.toDataURL("image/png", 1.0);
      const imgWidth = pageWidth - margin * 2;
      const imgHeight = (canvas.height / canvas.width) * imgWidth;
      if (y + imgHeight > pageHeight - margin) {
        doc.addPage();
        y = margin;
      }
      doc.addImage(imgData, "PNG", margin, y, imgWidth, imgHeight);
      y += imgHeight + 20;
    });

    doc.addPage();
    doc.autoTable({
      startY: margin,
      head: [[strings.exportId, strings.exportControl, strings.exportPregunta, strings.exportRespuesta, strings.exportNivel, strings.exportComentario]],
      body: filasPreguntas(),
      margin: { left: margin, right: margin },
      styles: { fontSize: 8, cellWidth: "wrap" },
      columnStyles: { 2: { cellWidth: 150 }, 5: { cellWidth: 120 } },
    });

    doc.save(nombreArchivo("pdf"));
  }

  function exportarExcel() {
    if (!ultimoResultado) return;
    if (typeof XLSX === "undefined") {
      alert("SheetJS no se cargó (revisa la conexión a internet).");
      return;
    }

    const wb = XLSX.utils.book_new();

    const resumen = [
      [strings.exportOrganizacion, ultimoResultado.organizacion],
      [strings.exportEvaluador, ultimoResultado.evaluador],
      [strings.exportFecha, ultimoResultado.fecha],
      [],
      [strings.exportDimension, strings.pctCumplimiento],
      ...DIMENSIONES.map((d) => [grupos[d], ultimoResultado.pct[d]]),
      [],
      [strings.exportGlobal, ultimoResultado.global],
    ];
    XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(resumen), "Resumen");

    const controlesRows = [[strings.exportId, strings.exportControl, strings.exportDimension, strings.pctCumplimiento, strings.exportNivelAuto]];
    ultimoResultado.resultadosControl.forEach((rc) => {
      controlesRows.push([rc.id, `${rc.codigo} ${rc.nombre}`, grupos[rc.grupo], rc.pctControl, rc.madurez]);
    });
    XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(controlesRows), "Controles");

    const preguntasRows = [[strings.exportId, strings.exportControl, strings.exportPregunta, strings.exportRespuesta, strings.exportNivel, strings.exportComentario]];
    filasPreguntas().forEach((fila) => preguntasRows.push(fila));
    XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(preguntasRows), "Preguntas");

    XLSX.writeFile(wb, nombreArchivo("xlsx"));
  }

  document.getElementById("btn-export-pdf")?.addEventListener("click", exportarPDF);
  document.getElementById("btn-export-excel")?.addEventListener("click", exportarExcel);

  inicializarNivelesManual();

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

    const popoverEls = [...document.querySelectorAll('[data-bs-toggle="popover"]')];
    const popovers = popoverEls.map((el) => new bootstrap.Popover(el));

    // Al abrir uno, cierra cualquier otro que haya quedado abierto
    document.addEventListener('show.bs.popover', function (e) {
      popovers.forEach((p) => {
        if (p._element !== e.target) p.hide();
      });
    });

    // Cerrar si se hace clic en cualquier otro lugar de la pagina
    document.addEventListener('click', function (e) {
      popoverEls.forEach((trigger, i) => {
        const popoverId = trigger.getAttribute('aria-describedby');
        const popoverEl = popoverId ? document.getElementById(popoverId) : null;
        const clickedTrigger = trigger.contains(e.target);
        const clickedInsidePopover = popoverEl && popoverEl.contains(e.target);
        if (!clickedTrigger && !clickedInsidePopover) {
          popovers[i].hide();
        }
      });
    });
  });

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const respuestas = {};
    const nivelesManual = {};
    controles.forEach((c) => {
      c.preguntas.forEach((p) => {
        const input = form.querySelector(`input[name="p${p.id}"]:checked`);
        const valor = input ? input.value : null;

        const spinnerNivel = document.getElementById(`nivel${p.id}`);
        const cajaComentario = document.getElementById(`comentario${p.id}`);
        const nivel = valor === "na" ? 0 : (spinnerNivel && spinnerNivel.value !== "" ? Number(spinnerNivel.value) : null);
        nivelesManual[p.id] = nivel;

        respuestas[p.id] = {
          respuesta: valor,
          nivel_madurez: nivel,
          // El comentario es opcional en general; siempre se envía lo que
          // el usuario haya escrito (aunque no sea obligatorio) para poder
          // guardarlo en BD y consultarlo a futuro.
          comentario: cajaComentario ? cajaComentario.value.trim() : "",
        };
      });
    });

    const respuestasSimples = {};
    Object.keys(respuestas).forEach((id) => { respuestasSimples[id] = respuestas[id].respuesta; });

    if (Object.values(respuestasSimples).some((v) => v === null) || Object.values(nivelesManual).some((v) => v === null)) {
      // El atributo required de cada campo ya evita esto en navegadores modernos,
      // pero se valida por si acaso.
      alert(strings.alertaIncompleto);
      return;
    }

    const faltaComentario = Object.values(respuestas).some((r) => {
      const requiereComentario = r.respuesta === "na" || r.nivel_madurez === 0;
      return requiereComentario && r.comentario.trim() === "";
    });
    if (faltaComentario) {
      alert(strings.alertaComentario);
      return;
    }

    const { pct, global, resultadosControl } = calcular(respuestasSimples);

    const organizacion = form.querySelector('[name="organizacion"]').value.trim();
    const evaluador = form.querySelector('[name="evaluador"]').value.trim();
    const fecha = form.querySelector('[name="fecha"]').value;
    guardarEvaluacion(organizacion, evaluador, fecha, respuestas);

    // Se guarda para que los botones de exportar PDF/Excel puedan generar
    // los archivos con los mismos resultados mostrados en el panel.
    ultimoResultado = { organizacion, evaluador, fecha, pct, global, resultadosControl, respuestas };

    // Cada bloque se aísla: si uno falla (p.ej. el CDN de Chart.js no cargó),
    // el resto del panel igual se muestra en vez de quedar todo en blanco.
    try { renderGlobal(global); } catch (err) { console.error("Error en renderGlobal:", err); }
    try { renderDimensionCards(pct); } catch (err) { console.error("Error en renderDimensionCards:", err); }
    try {
      if (typeof Chart === "undefined") {
        throw new Error("Chart.js no se cargó (revisa la conexión o el CDN en evaluacion-riesgos.php).");
      }
      renderCharts(pct);
      renderChartMadurezPreguntas(nivelesManual);
    } catch (err) {
      console.error("Error en renderCharts:", err);
      const chartsRow = document.getElementById("chart-barras")?.closest(".row");
      if (chartsRow) chartsRow.insertAdjacentHTML("beforebegin",
        '<p class="eval-submit-hint" style="color:var(--risk-crit)">No se pudieron cargar los gráficos (Chart.js). El resto de los resultados sí está disponible abajo.</p>');
    }
    try { renderRecomendaciones(pct); } catch (err) { console.error("Error en renderRecomendaciones:", err); }
    try { renderControlesDebiles(resultadosControl); } catch (err) { console.error("Error en renderControlesDebiles:", err); }

    dashboard.hidden = false;
    dashboard.scrollIntoView({ behavior: "smooth", block: "start" });
  });
})();
