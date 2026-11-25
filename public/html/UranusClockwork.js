/*
 * Simulation of the page UranusClockwork.html (wisdom UranusClockwork):
 * Uranus, the Epsilon ring and the shepherd moons Cordelia and Ophelia.
 * Language from ?lang=de|en, auto height via /js/htmlEmbed.js.
 */
(function () {
    'use strict';

    // =====================================================================
    // LANGUAGE (?lang=de | ?lang=en)
    // =====================================================================
    const I18N = {
        de: {
            pageTitle: 'Das Uranus-Uhrwerk: Epsilon-Ring & Schäfermonde',
            heading: 'Uranus & der Epsilon-Ring',
            subheading: 'Die kosmische Unruh & ihre Schäfermonde',
            modeAstro: 'Astronomisch',
            modeClock: 'Uhrwerk-Optik',
            canvasLabel: 'Animierte Darstellung von Uranus, dem Epsilon-Ring und den Schäfermonden Cordelia und Ophelia',
            infoTitle: 'Wie die Unruh funktioniert',
            infoText: 'Der Epsilon-Ring ist eierförmig (exzentrisch) und variiert stark in seiner Breite. <span class="c">Cordelia</span> (innen) schubst Partikel nach außen, <span class="o">Ophelia</span> (außen) bremst sie nach innen ab.',
            tickCaption: 'Uhrwerk-Takt',
            tick: 'TICK (Engstelle)',
            tack: 'TACK (Breitseite)',
            liveTitle: 'Live-Takte des Uhrwerks',
            day: 'Tag',
            beat1: '1. Schneller Takt (1 Umlauf)',
            orbits: 'Runden',
            beat1Duration: 'Dauer: 8 Std. 25 Min.',
            width: 'Breite:',
            beat2: '2. Hemmung (Cordelia-Impuls)',
            cycles: 'Zyklen',
            overtaking: 'Überholung: {days} Tage',
            resonance: 'Resonanz: 24:25',
            beat3: '3. Großer Zeiger (Präzession)',
            beat3Turn: '360°-Drehung: 264 Tage',
            beat3Rate: '1,36° / Tag',
            cordeliaLegend: 'Cordelia (innen)',
            cordeliaInfo: '8h 02m • Schub +',
            opheliaLegend: 'Ophelia (außen)',
            opheliaInfo: '9h 02m • Bremse −',
            pause: 'Pause',
            play: 'Weiter',
            reset: 'Zurücksetzen',
            speed: 'Tempo:',
            speedAria: 'Zeitraffer',
            speeds: ['15 Min. / Sek.', '1 Std. / Sek.', '4 Std. / Sek.', '1 Tag / Sek.', '10 Tage / Sek.'],
            toggleGravity: 'Gravitations-Impulse',
            toggleLabels: 'Beschriftungen',
            toggleShape: 'Unruh-Form betonen',
            toggleShapeTitle: 'Verstärkt die Eierform zur besseren Sichtbarkeit',
            labelNarrow: 'Eng (19,7 km)',
            labelWide: 'Breit (96,4 km)',
            labelCordelia: 'Cordelia (Schub +)',
            labelOphelia: 'Ophelia (Bremse −)'
        },
        en: {
            pageTitle: 'The Uranus Clockwork: Epsilon Ring & Shepherd Moons',
            heading: 'Uranus & the Epsilon Ring',
            subheading: 'The cosmic balance wheel & its shepherd moons',
            modeAstro: 'Astronomical',
            modeClock: 'Clockwork view',
            canvasLabel: 'Animation of Uranus, the Epsilon ring and the shepherd moons Cordelia and Ophelia',
            infoTitle: 'How the balance wheel works',
            infoText: 'The Epsilon ring is egg-shaped (eccentric) and its width varies strongly. <span class="c">Cordelia</span> (inside) pushes particles outwards, <span class="o">Ophelia</span> (outside) brakes them back inwards.',
            tickCaption: 'Clockwork beat',
            tick: 'TICK (narrow point)',
            tack: 'TOCK (wide side)',
            liveTitle: 'Live beats of the clockwork',
            day: 'Day',
            beat1: '1. Fast beat (1 orbit)',
            orbits: 'orbits',
            beat1Duration: 'Duration: 8 h 25 min',
            width: 'Width:',
            beat2: '2. Escapement (Cordelia impulse)',
            cycles: 'cycles',
            overtaking: 'Overtaking: {days} days',
            resonance: 'Resonance: 24:25',
            beat3: '3. Big hand (precession)',
            beat3Turn: '360° turn: 264 days',
            beat3Rate: '1.36° / day',
            cordeliaLegend: 'Cordelia (inner)',
            cordeliaInfo: '8h 02m • push +',
            opheliaLegend: 'Ophelia (outer)',
            opheliaInfo: '9h 02m • brake −',
            pause: 'Pause',
            play: 'Play',
            reset: 'Reset',
            speed: 'Speed:',
            speedAria: 'Time lapse',
            speeds: ['15 min / s', '1 h / s', '4 h / s', '1 day / s', '10 days / s'],
            toggleGravity: 'Gravity impulses',
            toggleLabels: 'Labels',
            toggleShape: 'Emphasise shape',
            toggleShapeTitle: 'Exaggerates the egg shape so that it is easier to see',
            labelNarrow: 'Narrow (19.7 km)',
            labelWide: 'Wide (96.4 km)',
            labelCordelia: 'Cordelia (push +)',
            labelOphelia: 'Ophelia (brake −)'
        }
    };

    const LANG = new URLSearchParams(window.location.search).get('lang') === 'en' ? 'en' : 'de';
    const T = I18N[LANG];

    // Number with the decimal separator of the language
    function fmt(value, digits) {
        const s = value.toFixed(digits);
        return LANG === 'de' ? s.replace('.', ',') : s;
    }

    function applyLanguage() {
        document.documentElement.lang = LANG;
        document.title = T.pageTitle;
        document.querySelectorAll('[data-i18n]').forEach(function (el) {
            el.textContent = T[el.getAttribute('data-i18n')];
        });
        // Static, trusted texts from I18N above (contain only <span> markup)
        document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
            el.innerHTML = T[el.getAttribute('data-i18n-html')];
        });
        document.querySelectorAll('[data-i18n-aria]').forEach(function (el) {
            el.setAttribute('aria-label', T[el.getAttribute('data-i18n-aria')]);
        });
        document.querySelectorAll('[data-i18n-title]').forEach(function (el) {
            el.setAttribute('title', T[el.getAttribute('data-i18n-title')]);
        });
    }

    // =====================================================================
    // PHYSICAL CONSTANTS & SIMULATION PARAMETERS
    // =====================================================================
    const canvas = document.getElementById('simCanvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('canvas-container');

    // Real time scales in hours
    const PERIOD_RING_HOURS = 8.417;       // 8 h 25 min
    const PERIOD_CORDELIA_HOURS = 8.033;   // 8 h 02 min
    const PERIOD_OPHELIA_HOURS = 9.033;    // 9 h 02 min
    const PERIOD_URANUS_HOURS = 17.233;    // 17 h 14 min (retrograde)
    const PRECESSION_DEG_PER_DAY = 1.3636; // 360° in about 264 days

    // Angular speeds in radians per simulated hour
    const OMEGA_RING = (Math.PI * 2) / PERIOD_RING_HOURS;
    const OMEGA_CORDELIA = (Math.PI * 2) / PERIOD_CORDELIA_HOURS;
    const OMEGA_OPHELIA = (Math.PI * 2) / PERIOD_OPHELIA_HOURS;
    const OMEGA_URANUS = -(Math.PI * 2) / PERIOD_URANUS_HOURS;
    const OMEGA_PRECESSION = ((PRECESSION_DEG_PER_DAY / 24) * Math.PI) / 180;

    // Cordelia overtakes the ring once per synodic period (computed from the periods above)
    const SYNODIC_PERIOD_HOURS = 1 / ((1 / PERIOD_CORDELIA_HOURS) - (1 / PERIOD_RING_HOURS));

    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const state = {
        isPlaying: !reducedMotion,   // respect "reduce motion": start paused
        visualMode: 'astro',         // 'astro' or 'clock'
        simHours: 0,
        hoursPerSecond: 1.0,
        showGravity: true,
        showLabels: true,
        exaggerateShape: true,       // exaggerates the eccentricity so that it is visible
        lastTimestamp: 0,
        visible: true                // false while the page is scrolled out of view
    };

    const SPEED_HOURS_PER_SEC = [0.25, 1.0, 4.0, 24.0, 240.0];

    // Particles of the Epsilon ring (rock and ice)
    const NUM_PARTICLES = 380;
    const particles = [];
    for (let i = 0; i < NUM_PARTICLES; i++) {
        particles.push({
            baseAngle: (i / NUM_PARTICLES) * Math.PI * 2 + (Math.random() - 0.5) * 0.02,
            radialOffset: (Math.random() - 0.5) * 0.85,
            size: 1.1 + Math.random() * 1.6,
            brightness: 0.5 + Math.random() * 0.5
        });
    }

    // Background stars
    const stars = [];
    for (let i = 0; i < 140; i++) {
        stars.push({ x: Math.random(), y: Math.random(), r: 0.4 + Math.random() * 1.2, alpha: 0.2 + Math.random() * 0.7 });
    }

    // =====================================================================
    // CANVAS SIZE
    // =====================================================================
    function resizeCanvas() {
        const rect = container.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = Math.max(1, Math.round(rect.width * dpr));
        canvas.height = Math.max(1, Math.round(rect.height * dpr));
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    // =====================================================================
    // UI
    // =====================================================================
    function updatePlayButton() {
        document.getElementById('play-icon').textContent = state.isPlaying ? '❚❚' : '▶';
        document.getElementById('play-text').textContent = state.isPlaying ? T.pause : T.play;
    }

    function togglePlay() {
        state.isPlaying = !state.isPlaying;
        updatePlayButton();
    }

    function resetSimulation() {
        state.simHours = 0;
        if (!state.isPlaying) drawFrame();
    }

    function updateSpeedFromSlider(val) {
        const idx = Math.min(Math.max(parseInt(val, 10) - 1, 0), SPEED_HOURS_PER_SEC.length - 1);
        state.hoursPerSecond = SPEED_HOURS_PER_SEC[idx];
        document.getElementById('speed-label').textContent = T.speeds[idx];
    }

    function toggleOption(opt, btn) {
        if (opt === 'gravity') state.showGravity = !state.showGravity;
        if (opt === 'labels') state.showLabels = !state.showLabels;
        if (opt === 'exaggerate') state.exaggerateShape = !state.exaggerateShape;
        const active = opt === 'gravity' ? state.showGravity : (opt === 'labels' ? state.showLabels : state.exaggerateShape);
        btn.classList.toggle('off', !active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        if (!state.isPlaying) drawFrame();
    }

    function setVisualMode(mode) {
        state.visualMode = mode;
        const btnAstro = document.getElementById('btn-mode-astro');
        const btnClock = document.getElementById('btn-mode-clock');
        btnAstro.classList.toggle('active-astro', mode === 'astro');
        btnClock.classList.toggle('active-clock', mode === 'clock');
        document.getElementById('main-title').classList.toggle('clock', mode === 'clock');
        if (!state.isPlaying) drawFrame();
    }

    // =====================================================================
    // GEOMETRY OF THE ECCENTRIC EPSILON RING
    // =====================================================================
    // Radius of inner and outer edge for an angle relative to the periapsis
    function getRingRadii(relAngle, baseRadius, exag) {
        // Real eccentricity is 0.0079, shown exaggerated so that it can be seen
        const ecc = exag ? 0.085 : 0.022;
        // Ring width varies between ~20 km (periapsis) and ~96 km (apoapsis)
        const minWidth = baseRadius * (exag ? 0.042 : 0.022);
        const maxWidth = baseRadius * (exag ? 0.195 : 0.085);

        const factor = (1 - Math.cos(relAngle)) / 2; // 0 at periapsis, 1 at apoapsis
        const width = minWidth + factor * (maxWidth - minWidth);
        const centerRadius = baseRadius * (1 - ecc * Math.cos(relAngle));

        return {
            rInner: centerRadius - width * 0.5,
            rOuter: centerRadius + width * 0.5,
            rMid: centerRadius,
            widthKm: 19.7 + factor * (96.4 - 19.7)
        };
    }

    // =====================================================================
    // DRAWING
    // =====================================================================
    const CANVAS_FONT = 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';

    // Draws centred text, but shifted so that it stays inside the canvas.
    // x, y are relative to the translated origin (cx, cy).
    function fillTextInside(text, x, y, cx, cy) {
        const half = ctx.measureText(text).width / 2;
        const minX = -cx + 4 + half;
        const maxX = cx - 4 - half;
        ctx.fillText(text, Math.min(Math.max(x, minX), maxX), Math.min(Math.max(y, -cy + 14), cy - 6));
    }

    function drawBackground(w, h) {
        const grad = ctx.createRadialGradient(w / 2, h / 2, 10, w / 2, h / 2, Math.max(w, h) * 0.7);
        if (state.visualMode === 'astro') {
            grad.addColorStop(0, '#0c162d');
            grad.addColorStop(0.5, '#060b19');
            grad.addColorStop(1, '#03050c');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, w, h);
            for (const s of stars) {
                ctx.fillStyle = `rgba(226, 232, 240, ${s.alpha})`;
                ctx.beginPath();
                ctx.arc(s.x * w, s.y * h, s.r, 0, Math.PI * 2);
                ctx.fill();
            }
        } else {
            // Fine mechanics background
            grad.addColorStop(0, '#1c160e');
            grad.addColorStop(0.6, '#0f0c08');
            grad.addColorStop(1, '#070503');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, w, h);
            ctx.strokeStyle = 'rgba(217, 119, 6, 0.06)';
            ctx.lineWidth = 1;
            const step = 40;
            for (let x = (w / 2) % step; x < w; x += step) {
                ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, h); ctx.stroke();
            }
            for (let y = (h / 2) % step; y < h; y += step) {
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke();
            }
        }
    }

    // Outer dial and the big hand (precession)
    function drawClockworkDial(cx, cy, maxR, precessionAngle) {
        const isClock = state.visualMode === 'clock';
        const dialR = maxR * 0.94;

        ctx.save();
        ctx.translate(cx, cy);

        ctx.beginPath();
        ctx.arc(0, 0, dialR, 0, Math.PI * 2);
        ctx.strokeStyle = isClock ? 'rgba(234, 179, 8, 0.45)' : 'rgba(56, 189, 248, 0.14)';
        ctx.lineWidth = isClock ? 3 : 1;
        ctx.stroke();

        if (isClock) {
            ctx.beginPath();
            ctx.arc(0, 0, dialR - 12, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(217, 119, 6, 0.3)';
            ctx.lineWidth = 1.5;
            ctx.stroke();
        }

        for (let i = 0; i < 60; i++) {
            const a = (i / 60) * Math.PI * 2;
            const isMajor = i % 5 === 0;
            const len = isMajor ? 10 : 5;
            ctx.beginPath();
            ctx.moveTo(Math.cos(a) * (dialR - len), Math.sin(a) * (dialR - len));
            ctx.lineTo(Math.cos(a) * dialR, Math.sin(a) * dialR);
            ctx.strokeStyle = isClock
                ? (isMajor ? 'rgba(250, 204, 21, 0.8)' : 'rgba(217, 119, 6, 0.4)')
                : (isMajor ? 'rgba(56, 189, 248, 0.35)' : 'rgba(56, 189, 248, 0.12)');
            ctx.lineWidth = isMajor ? 2 : 1;
            ctx.stroke();
        }

        // Big hand for the precession (264 days)
        ctx.save();
        ctx.rotate(precessionAngle);
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(dialR - 14, 0);
        ctx.strokeStyle = isClock ? 'rgba(245, 158, 11, 0.55)' : 'rgba(251, 191, 36, 0.22)';
        ctx.setLineDash([4, 4]);
        ctx.lineWidth = 1.5;
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.fillStyle = '#fbbf24';
        ctx.beginPath();
        ctx.arc(dialR - 6, 0, 4, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        ctx.restore();
    }

    // The fine inner rings of Uranus (Alpha, Beta, Eta, Gamma, Delta)
    function drawInnerRings(cx, cy, baseRingR) {
        const innerRadii = [0.72, 0.76, 0.81, 0.84, 0.87];
        ctx.save();
        ctx.translate(cx, cy);
        for (const f of innerRadii) {
            ctx.beginPath();
            ctx.arc(0, 0, baseRingR * f, 0, Math.PI * 2);
            ctx.strokeStyle = state.visualMode === 'clock' ? 'rgba(217, 119, 6, 0.16)' : 'rgba(148, 163, 184, 0.12)';
            ctx.lineWidth = 1;
            ctx.stroke();
        }
        ctx.restore();
    }

    // The planet Uranus in the centre
    function drawUranus(cx, cy, planetR, uranusRot) {
        ctx.save();
        ctx.translate(cx, cy);

        const glow = ctx.createRadialGradient(0, 0, planetR * 0.8, 0, 0, planetR * 1.45);
        if (state.visualMode === 'clock') {
            glow.addColorStop(0, 'rgba(56, 189, 248, 0.25)');
            glow.addColorStop(1, 'rgba(56, 189, 248, 0)');
        } else {
            glow.addColorStop(0, 'rgba(103, 232, 249, 0.3)');
            glow.addColorStop(1, 'rgba(6, 182, 212, 0)');
        }
        ctx.fillStyle = glow;
        ctx.beginPath();
        ctx.arc(0, 0, planetR * 1.45, 0, Math.PI * 2);
        ctx.fill();

        const bodyGrad = ctx.createRadialGradient(-planetR * 0.3, -planetR * 0.3, planetR * 0.1, 0, 0, planetR);
        bodyGrad.addColorStop(0, '#a5f3fc');
        bodyGrad.addColorStop(0.55, '#38bdf8');
        bodyGrad.addColorStop(1, '#0369a1');
        ctx.fillStyle = bodyGrad;
        ctx.beginPath();
        ctx.arc(0, 0, planetR, 0, Math.PI * 2);
        ctx.fill();

        // Rotating cloud bands and pole mark (17 h 14 min rotation)
        ctx.save();
        ctx.rotate(uranusRot);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.22)';
        ctx.lineWidth = 1.5;
        for (let i = 0; i < 3; i++) {
            ctx.beginPath();
            ctx.arc(0, 0, planetR * (0.35 + i * 0.22), 0.2, Math.PI - 0.2);
            ctx.stroke();
        }
        // Uranus lies on its side, so we look almost onto the pole
        ctx.fillStyle = 'rgba(224, 242, 254, 0.6)';
        ctx.beginPath();
        ctx.arc(0, 0, 3, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        if (state.visualMode === 'clock') {
            ctx.strokeStyle = '#d97706';
            ctx.lineWidth = 2.5;
            ctx.beginPath();
            ctx.arc(0, 0, planetR + 2, 0, Math.PI * 2);
            ctx.stroke();
        }

        if (state.showLabels) {
            ctx.fillStyle = '#e0f2fe';
            ctx.font = '600 11px ' + CANVAS_FONT;
            ctx.textAlign = 'center';
            ctx.fillText('URANUS', 0, planetR + 16);
        }

        ctx.restore();
    }

    // The egg-shaped Epsilon ring and its particles
    function drawEpsilonRing(cx, cy, baseRingR, precessionAngle, ringPhaseAngle) {
        const segments = 180;
        const isClock = state.visualMode === 'clock';

        ctx.save();
        ctx.translate(cx, cy);

        // 1. Ring band as polygon between inner and outer edge
        ctx.beginPath();
        for (let i = 0; i <= segments; i++) {
            const theta = (i / segments) * Math.PI * 2;
            const { rOuter } = getRingRadii(theta - precessionAngle, baseRingR, state.exaggerateShape);
            const x = Math.cos(theta) * rOuter;
            const y = Math.sin(theta) * rOuter;
            if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        }
        for (let i = segments; i >= 0; i--) {
            const theta = (i / segments) * Math.PI * 2;
            const { rInner } = getRingRadii(theta - precessionAngle, baseRingR, state.exaggerateShape);
            ctx.lineTo(Math.cos(theta) * rInner, Math.sin(theta) * rInner);
        }
        ctx.closePath();

        if (isClock) {
            ctx.fillStyle = 'rgba(217, 119, 6, 0.22)';
            ctx.fill();
            ctx.strokeStyle = '#f59e0b';
            ctx.lineWidth = 1.6;
            ctx.stroke();
        } else {
            ctx.fillStyle = 'rgba(148, 163, 184, 0.18)';
            ctx.fill();
            ctx.strokeStyle = 'rgba(186, 230, 253, 0.55)';
            ctx.lineWidth = 1.2;
            ctx.stroke();
        }

        // 2. Particles racing through the narrow and the wide zone
        for (const p of particles) {
            const theta = p.baseAngle + ringPhaseAngle;
            const { rMid, rOuter, rInner } = getRingRadii(theta - precessionAngle, baseRingR, state.exaggerateShape);
            const r = rMid + p.radialOffset * (rOuter - rInner);
            ctx.fillStyle = isClock
                ? `rgba(253, 224, 71, ${p.brightness * 0.85})`
                : `rgba(224, 242, 254, ${p.brightness * 0.85})`;
            ctx.beginPath();
            ctx.arc(Math.cos(theta) * r, Math.sin(theta) * r, p.size, 0, Math.PI * 2);
            ctx.fill();
        }

        // 3. Reference marker that follows one orbit (8 h 25 min)
        const markerTheta = ringPhaseAngle;
        const markerGeom = getRingRadii(markerTheta - precessionAngle, baseRingR, state.exaggerateShape);
        ctx.fillStyle = '#38bdf8';
        ctx.shadowColor = '#38bdf8';
        ctx.shadowBlur = 10;
        ctx.beginPath();
        ctx.arc(Math.cos(markerTheta) * markerGeom.rMid, Math.sin(markerTheta) * markerGeom.rMid, 4.5, 0, Math.PI * 2);
        ctx.fill();
        ctx.shadowBlur = 0;

        // Labels of periapsis (narrow) and apoapsis (wide)
        if (state.showLabels) {
            const periGeom = getRingRadii(0, baseRingR, state.exaggerateShape);
            const apoGeom = getRingRadii(Math.PI, baseRingR, state.exaggerateShape);
            ctx.font = '500 10px ' + CANVAS_FONT;
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            fillTextInside(T.labelNarrow, Math.cos(precessionAngle) * (periGeom.rOuter + 14), Math.sin(precessionAngle) * (periGeom.rOuter + 14), cx, cy);
            fillTextInside(T.labelWide, Math.cos(precessionAngle + Math.PI) * (apoGeom.rOuter + 16), Math.sin(precessionAngle + Math.PI) * (apoGeom.rOuter + 16), cx, cy);
        }

        ctx.restore();
        return { markerWidthKm: markerGeom.widthKm };
    }

    // The shepherd moons Cordelia and Ophelia and their gravity impulses
    function drawShepherdMoons(cx, cy, baseRingR, precessionAngle, cordeliaAngle, opheliaAngle) {
        const cordeliaR = baseRingR * 0.905;
        const opheliaR = baseRingR * 1.135;
        const cxPos = Math.cos(cordeliaAngle) * cordeliaR;
        const cyPos = Math.sin(cordeliaAngle) * cordeliaR;
        const oxPos = Math.cos(opheliaAngle) * opheliaR;
        const oyPos = Math.sin(opheliaAngle) * opheliaR;

        ctx.save();
        ctx.translate(cx, cy);

        // Orbits of the shepherd moons
        ctx.setLineDash([3, 5]);
        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(52, 211, 153, 0.22)';
        ctx.beginPath(); ctx.arc(0, 0, cordeliaR, 0, Math.PI * 2); ctx.stroke();
        ctx.strokeStyle = 'rgba(251, 113, 133, 0.22)';
        ctx.beginPath(); ctx.arc(0, 0, opheliaR, 0, Math.PI * 2); ctx.stroke();
        ctx.setLineDash([]);

        if (state.showGravity) {
            const pulse = 0.55 + 0.45 * Math.sin(state.simHours * 6);

            // Cordelia: push outwards and forwards (in the direction of flight)
            const cRing = getRingRadii(cordeliaAngle - precessionAngle, baseRingR, state.exaggerateShape);
            const cTarget = cordeliaAngle + 0.14;
            const cTx = Math.cos(cTarget) * cRing.rInner;
            const cTy = Math.sin(cTarget) * cRing.rInner;
            ctx.strokeStyle = `rgba(52, 211, 153, ${0.45 + pulse * 0.45})`;
            ctx.lineWidth = 2;
            ctx.beginPath(); ctx.moveTo(cxPos, cyPos); ctx.lineTo(cTx, cTy); ctx.stroke();
            ctx.fillStyle = `rgba(52, 211, 153, ${0.5 + pulse * 0.5})`;
            ctx.beginPath(); ctx.arc(cTx, cTy, 3 + pulse * 1.5, 0, Math.PI * 2); ctx.fill();

            // Ophelia: brake inwards and backwards (against the direction of flight)
            const oRing = getRingRadii(opheliaAngle - precessionAngle, baseRingR, state.exaggerateShape);
            const oTarget = opheliaAngle - 0.14;
            const oTx = Math.cos(oTarget) * oRing.rOuter;
            const oTy = Math.sin(oTarget) * oRing.rOuter;
            ctx.strokeStyle = `rgba(251, 113, 133, ${0.45 + pulse * 0.45})`;
            ctx.lineWidth = 2;
            ctx.beginPath(); ctx.moveTo(oxPos, oyPos); ctx.lineTo(oTx, oTy); ctx.stroke();
            ctx.fillStyle = `rgba(251, 113, 133, ${0.5 + pulse * 0.5})`;
            ctx.beginPath(); ctx.arc(oTx, oTy, 3 + pulse * 1.5, 0, Math.PI * 2); ctx.fill();
        }

        ctx.fillStyle = '#34d399';
        ctx.shadowColor = '#10b981';
        ctx.shadowBlur = 10;
        ctx.beginPath(); ctx.arc(cxPos, cyPos, 5.5, 0, Math.PI * 2); ctx.fill();
        ctx.fillStyle = '#fb7185';
        ctx.shadowColor = '#f43f5e';
        ctx.beginPath(); ctx.arc(oxPos, oyPos, 6, 0, Math.PI * 2); ctx.fill();
        ctx.shadowBlur = 0;

        if (state.showLabels) {
            ctx.font = '600 11px ' + CANVAS_FONT;
            ctx.textAlign = 'left';
            ctx.fillStyle = '#6ee7b7';
            ctx.fillText(T.labelCordelia, cxPos + 9, cyPos + 4);
            ctx.fillStyle = '#fda4af';
            ctx.fillText(T.labelOphelia, oxPos + 10, oyPos + 4);
        }

        ctx.restore();
    }

    // =====================================================================
    // TELEMETRY
    // =====================================================================
    const el = {
        days: document.getElementById('sim-days'),
        orbitCount: document.getElementById('stat-orbit-count'),
        barOrbit: document.getElementById('bar-orbit'),
        width: document.getElementById('stat-width'),
        synodicCount: document.getElementById('stat-synodic-count'),
        barSynodic: document.getElementById('bar-synodic'),
        precDeg: document.getElementById('stat-precession-deg'),
        barPrec: document.getElementById('bar-precession'),
        tickLabel: document.getElementById('tick-label'),
        tickLed: document.getElementById('tick-led')
    };

    function updateTelemetry(markerInfo) {
        const days = state.simHours / 24;
        el.days.textContent = `${T.day} ${fmt(days, 2)}`;

        // 1. Fast beat: one orbit of the ring (8 h 25 min)
        const orbits = state.simHours / PERIOD_RING_HOURS;
        el.orbitCount.textContent = `${fmt(orbits, 2)} ${T.orbits}`;
        el.barOrbit.style.width = `${(orbits % 1) * 100}%`;
        el.width.textContent = `${fmt(markerInfo.markerWidthKm, 1)} km`;

        // 2. Escapement: Cordelia overtakes the ring
        const synodicCycles = state.simHours / SYNODIC_PERIOD_HOURS;
        el.synodicCount.textContent = `${fmt(synodicCycles, 2)} ${T.cycles}`;
        el.barSynodic.style.width = `${(synodicCycles % 1) * 100}%`;

        // 3. Precession of the ellipse (360° in about 264 days)
        const precDeg = (days * PRECESSION_DEG_PER_DAY) % 360;
        el.precDeg.textContent = `${fmt(precDeg, 1)}°`;
        el.barPrec.style.width = `${(precDeg / 360) * 100}%`;

        // "Tick-tock" of the balance wheel (narrow point / wide side)
        const isTick = Math.floor(orbits * 2) % 2 === 0;
        el.tickLabel.textContent = isTick ? T.tick : T.tack;
        el.tickLabel.classList.toggle('tack', !isTick);
        el.tickLed.classList.toggle('tack', !isTick);
    }

    // =====================================================================
    // MAIN LOOP
    // =====================================================================
    function drawFrame() {
        const rect = container.getBoundingClientRect();
        const w = rect.width;
        const h = rect.height;
        const cx = w / 2;
        const cy = h / 2;
        const maxR = Math.min(w, h) * 0.47;
        const baseRingR = maxR * 0.72;
        const planetR = baseRingR * 0.46;

        const ringPhaseAngle = state.simHours * OMEGA_RING;
        const precessionAngle = state.simHours * OMEGA_PRECESSION;

        drawBackground(w, h);
        drawClockworkDial(cx, cy, maxR, precessionAngle);
        drawInnerRings(cx, cy, baseRingR);
        const markerInfo = drawEpsilonRing(cx, cy, baseRingR, precessionAngle, ringPhaseAngle);
        drawShepherdMoons(cx, cy, baseRingR, precessionAngle, state.simHours * OMEGA_CORDELIA, state.simHours * OMEGA_OPHELIA);
        drawUranus(cx, cy, planetR, state.simHours * OMEGA_URANUS);
        updateTelemetry(markerInfo);
    }

    function animate(timestamp) {
        if (!state.lastTimestamp) state.lastTimestamp = timestamp;
        const dtSec = Math.min((timestamp - state.lastTimestamp) / 1000, 0.1);
        state.lastTimestamp = timestamp;

        // Only work while visible: saves CPU when the wisdom is scrolled further down
        if (state.visible) {
            if (state.isPlaying) state.simHours += dtSec * state.hoursPerSecond;
            drawFrame();
        }
        requestAnimationFrame(animate);
    }

    // =====================================================================
    // AUTO HEIGHT: tell the embedding page how tall this page is
    // =====================================================================
    let lastReportedHeight = 0;
    function reportHeight() {
        if (window.parent === window) return;              // opened directly, not embedded
        const height = Math.ceil(document.documentElement.getBoundingClientRect().height);
        if (Math.abs(height - lastReportedHeight) < 2) return;
        lastReportedHeight = height;
        try {
            window.parent.postMessage({ type: 'guru-html-height', height: height }, window.location.origin);
        } catch (e) { /* parent on another origin: nothing to do */ }
    }

    // =====================================================================
    // START
    // =====================================================================
    applyLanguage();
    updatePlayButton();
    document.getElementById('stat-synodic-period').textContent =
        T.overtaking.replace('{days}', fmt(SYNODIC_PERIOD_HOURS / 24, 2));

    document.getElementById('btn-play-pause').addEventListener('click', togglePlay);
    document.getElementById('btn-reset').addEventListener('click', resetSimulation);
    document.getElementById('btn-mode-astro').addEventListener('click', function () { setVisualMode('astro'); });
    document.getElementById('btn-mode-clock').addEventListener('click', function () { setVisualMode('clock'); });
    const slider = document.getElementById('speed-slider');
    slider.addEventListener('input', function () { updateSpeedFromSlider(this.value); });
    updateSpeedFromSlider(slider.value);
    [['toggle-gravity', 'gravity'], ['toggle-labels', 'labels'], ['toggle-exaggerate', 'exaggerate']].forEach(function (pair) {
        const btn = document.getElementById(pair[0]);
        btn.addEventListener('click', function () { toggleOption(pair[1], btn); });
    });

    resizeCanvas();
    if ('ResizeObserver' in window) {
        new ResizeObserver(function () { resizeCanvas(); drawFrame(); }).observe(container);
        new ResizeObserver(reportHeight).observe(document.body);
    } else {
        window.addEventListener('resize', function () { resizeCanvas(); drawFrame(); reportHeight(); });
    }
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            state.visible = entries[0].isIntersecting;
            state.lastTimestamp = 0;   // no time jump after scrolling back
        }).observe(container);
    }

    drawFrame();
    reportHeight();
    window.addEventListener('load', reportHeight);
    requestAnimationFrame(animate);
})();
