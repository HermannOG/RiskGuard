(function () {
    "use strict";

    const dataEl = document.getElementById("ver-eval-data");
    if (!dataEl) return;

    const {
        pct, global, resultadosControl, nivelesManual, preguntasDetalle,
        grupos, recomendaciones, nivelesMadurez, strings,
    } = JSON.parse(dataEl.textContent);

    const DIMENSIONES = ["C", "I", "D"];
    let chartMadurezPreguntas = null;

    function semaforo(p) {
        if (p >= 80) return { color: "var(--risk-low)", nivel: strings.verde, texto: strings.verdeTexto };
        if (p >= 60) return { color: "var(--risk-mid)", nivel: strings.amarillo, texto: strings.amarilloTexto };
        return { color: "var(--risk-crit)", nivel: strings.rojo, texto: strings.rojoTexto };
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

    function renderGlobal() {
        const s = semaforo(global);
        document.getElementById("eval-global-pct").textContent = Math.round(global) + "%";
        document.getElementById("eval-global-pct").style.color = s.color;
        const badge = document.getElementById("eval-global-badge");
        badge.textContent = s.nivel;
        badge.style.background = s.color;
        document.getElementById("eval-global-text").textContent = s.texto;
    }

    function renderDimensionCards() {
        const container = document.getElementById("eval-dimension-cards");
        container.innerHTML = "";
        DIMENSIONES.forEach((dim) => {
            const s = semaforo(pct[dim]);
            const col = document.createElement("div");
            col.className = "col-md-4";
            col.innerHTML = `
        <div class="eval-dim-card" style="--dim-color:${s.color}">
          <span class="eval-dim-tag">${grupos[dim]}</span>
          <div class="eval-dim-pct">${Math.round(pct[dim])}%</div>
          <span class="eval-badge" style="background:${s.color}">${s.nivel}</span>
          <p class="eval-dim-interp">${s.texto}</p>
        </div>`;
            container.appendChild(col);
        });
    }

    function renderCharts() {
        const ctxBarras = document.getElementById("chart-barras").getContext("2d");
        const ctxCircular = document.getElementById("chart-circular").getContext("2d");
        const colores = DIMENSIONES.map((d) => resolverColorCss(semaforo(pct[d]).color));

        new Chart(ctxBarras, {
            type: "bar",
            data: {
                labels: DIMENSIONES.map((d) => grupos[d]),
                datasets: [{
                    label: strings.pctCumplimiento || "% de cumplimiento",
                    data: DIMENSIONES.map((d) => Math.round(pct[d])),
                    backgroundColor: colores,
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { color: "#9AA7C2" }, grid: { color: "#23314B" } },
                    x: { ticks: { color: "#9AA7C2" }, grid: { display: false } },
                },
                plugins: { legend: { display: false } },
            },
        });

        new Chart(ctxCircular, {
            type: "doughnut",
            data: {
                labels: [strings.cumplimiento || "Cumplimiento", strings.brecha || "Brecha"],
                datasets: [{
                    data: [Math.round(global), 100 - Math.round(global)],
                    backgroundColor: [resolverColorCss(semaforo(global).color), "#23314B"],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: { legend: { position: "bottom", labels: { color: "#9AA7C2" } } },
            },
        });
    }

    function renderChartMadurezPreguntas() {
        const canvas = document.getElementById("chart-madurez-preguntas");
        if (!canvas) return;

        const ids = Object.keys(nivelesManual).map(Number).sort((a, b) => a - b);
        const textoPorId = {};
        preguntasDetalle.forEach((p) => { textoPorId[p.id] = p.texto; });

        // Más espacio por pregunta (antes 28px, muy apretado con 26+ preguntas)
        // y un tope más alto para que quepan cómodas sin solaparse.
        const alturaCalculada = Math.max(300, Math.min(ids.length, 40) * 34);
        canvas.parentElement.style.height = Math.min(alturaCalculada, 900) + "px";
        canvas.parentElement.style.overflowY = "auto";

        const niveles = ids.map((id) => nivelesManual[id] ?? 0);
        const colores = niveles.map((n) => resolverColorCss(colorPorNivelManual(n)));

        if (chartMadurezPreguntas) { chartMadurezPreguntas.destroy(); chartMadurezPreguntas = null; }

        chartMadurezPreguntas = new Chart(canvas.getContext("2d"), {
            type: "bar",
            data: {
                labels: ids.map((id) => `${id}. ${textoPorId[id] || ""}`),
                datasets: [{
                    label: strings.nivelMadurezChart,
                    data: niveles,
                    backgroundColor: colores,
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8,
                }],
            },
            options: {
                indexAxis: "y",
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 100,
                layout: { padding: { left: 4, right: 12 } },
                scales: {
                    x: { beginAtZero: true, max: 5, ticks: { color: "#9AA7C2", stepSize: 1 }, grid: { color: "#23314B" } },
                    y: { ticks: { color: "#9AA7C2", autoSkip: false, font: { size: 11 } }, grid: { display: false } },
                },
                plugins: { legend: { display: false } },
            },
        });
    }

    function renderRecomendaciones() {
        const lista = document.getElementById("eval-recos-list");
        lista.innerHTML = "";
        const debiles = DIMENSIONES.filter((d) => pct[d] < 80).sort((a, b) => pct[a] - pct[b]);

        if (debiles.length === 0) {
            lista.innerHTML = `<li>${strings.sinBrechas}</li>`;
            return;
        }
        debiles.forEach((d) => {
            const li = document.createElement("li");
            li.innerHTML = `<strong>${grupos[d]} (${Math.round(pct[d])}%):</strong> ${recomendaciones[d]}`;
            lista.appendChild(li);
        });
    }

    function renderControlesDebiles() {
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

    try { renderGlobal(); } catch (e) { console.error(e); }
    try { renderDimensionCards(); } catch (e) { console.error(e); }
    try {
        if (typeof Chart === "undefined") throw new Error("Chart.js no se cargó.");
        renderCharts();
        renderChartMadurezPreguntas();
    } catch (e) {
        console.error(e);
        const row = document.getElementById("chart-barras")?.closest(".row");
        if (row) row.insertAdjacentHTML("beforebegin",
            '<p class="eval-submit-hint" style="color:var(--risk-crit)">No se pudieron cargar los gráficos (Chart.js).</p>');
    }
    try { renderRecomendaciones(); } catch (e) { console.error(e); }
    try { renderControlesDebiles(); } catch (e) { console.error(e); }
})();