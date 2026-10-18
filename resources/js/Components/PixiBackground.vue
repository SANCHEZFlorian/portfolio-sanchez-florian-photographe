<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Application, Graphics } from 'pixi.js';

const props = defineProps({
    mode: {
        type: String,
        default: 'bokeh', // 'bokeh' (cercles flottants originaux animés) ou 'admin' (constellation subtile)
    },
    opacity: {
        type: Number,
        default: 1,
    },
});

const containerRef = ref(null);
let app = null;
let orbs = [];
let particles = [];
let graphics = null;
let mouseX = -1000;
let mouseY = -1000;
let targetMouseX = -1000;
let targetMouseY = -1000;
let scrollY = 0;
let targetScrollY = 0;
let isPaused = false;

// Couleurs de la charte : Bleu primaire (#293ecc / #3a52e0) et Violet accent (#9d4edd / #7b2cbf)
const adminColors = [0x3a52e0, 0x818cf8, 0x9d4edd, 0x38bdf8];

function initAmbientOrbs(width, height) {
    orbs = [];
    // 7 grands cercles ambiants purs (reproduction fidèle des 6 sphères originales, avec mouvement vivant)
    const count = width < 768 ? 5 : 7;
    const presets = [
        { xRatio: 0.05, yRatio: 0.15, radius: 180, color: 0x293ecc, alpha: 0.08 },
        { xRatio: 0.88, yRatio: 0.65, radius: 140, color: 0x9d4edd, alpha: 0.07 },
        { xRatio: 0.30, yRatio: 0.78, radius: 110, color: 0x293ecc, alpha: 0.07 },
        { xRatio: 0.72, yRatio: 0.28, radius: 85, color: 0x9d4edd, alpha: 0.05 },
        { xRatio: 0.82, yRatio: 0.88, radius: 75, color: 0x293ecc, alpha: 0.06 },
        { xRatio: 0.94, yRatio: 0.05, radius: 160, color: 0x9d4edd, alpha: 0.05 },
        { xRatio: 0.18, yRatio: 0.48, radius: 130, color: 0x3a52e0, alpha: 0.06 },
    ];

    for (let i = 0; i < count; i++) {
        const p = presets[i % presets.length];
        const depth = 0.3 + (i % 3) * 0.3; // Facteur de parallaxe
        orbs.push({
            x: p.xRatio * width,
            y: p.yRatio * height,
            baseX: p.xRatio * width,
            baseY: p.yRatio * height,
            radius: p.radius,
            baseRadius: p.radius,
            color: p.color,
            alpha: p.alpha,
            baseAlpha: p.alpha,
            speedX: (Math.random() - 0.5) * 0.35,
            speedY: (Math.random() - 0.5) * 0.3,
            pulseSpeed: 0.006 + Math.random() * 0.008,
            pulseOffset: Math.random() * Math.PI * 2,
            depth,
        });
    }
}

function initAdminParticles(width, height) {
    particles = [];
    const count = 48;

    for (let i = 0; i < count; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.35,
            vy: (Math.random() - 0.5) * 0.35,
            radius: 1.2 + Math.random() * 2,
            color: adminColors[Math.floor(Math.random() * adminColors.length)],
            alpha: 0.25 + Math.random() * 0.45,
            hasGlow: Math.random() > 0.6,
        });
    }
}

function handleMouseMove(e) {
    targetMouseX = e.clientX;
    targetMouseY = e.clientY;
}

function handleMouseLeave() {
    targetMouseX = -1000;
    targetMouseY = -1000;
}

function handleScroll() {
    targetScrollY = window.scrollY || window.pageYOffset;
}

function evaluatePause() {
    if (!app) return;
    const shouldPause = document.hidden;
    if (shouldPause && !isPaused) {
        isPaused = true;
        app.ticker.stop();
    } else if (!shouldPause && isPaused) {
        isPaused = false;
        app.ticker.start();
    }
}

onMounted(async () => {
    if (!containerRef.value) return;

    try {
        app = new Application();
        await app.init({
            resizeTo: window,
            backgroundAlpha: 0,
            antialias: true,
            autoDensity: true,
            powerPreference: 'low-power',
        });

        if (containerRef.value) {
            containerRef.value.appendChild(app.canvas);
        }

        graphics = new Graphics();
        app.stage.addChild(graphics);

        const width = window.innerWidth;
        const height = window.innerHeight;

        if (props.mode === 'bokeh') {
            initAmbientOrbs(width, height);
        } else {
            initAdminParticles(width, height);
        }

        let time = 0;

        app.ticker.add(() => {
            time += 0.016;

            // Lissage de position souris et défilement
            mouseX += (targetMouseX - mouseX) * 0.06;
            mouseY += (targetMouseY - mouseY) * 0.06;
            scrollY += (targetScrollY - scrollY) * 0.08;

            graphics.clear();
            const w = window.innerWidth;
            const h = window.innerHeight;

            if (props.mode === 'bokeh') {
                for (let i = 0; i < orbs.length; i++) {
                    const orb = orbs[i];

                    // Déplacement flottant continu
                    orb.x += orb.speedX;
                    orb.y += orb.speedY;

                    // Respiration douce du rayon et de l'opacité
                    const pulse = Math.sin(time * orb.pulseSpeed * 60 + orb.pulseOffset);
                    const currentRadius = orb.baseRadius * (1 + pulse * 0.08);
                    const currentAlpha = orb.baseAlpha * (1 + pulse * 0.18);

                    // Décalage de parallaxe vertical lié au scroll
                    const parallaxY = orb.y - scrollY * (0.15 + orb.depth * 0.3);

                    // Bouclage infini aux limites de l'écran
                    if (orb.x < -orb.baseRadius * 1.5) orb.x = w + orb.baseRadius;
                    if (orb.x > w + orb.baseRadius * 1.5) orb.x = -orb.baseRadius;
                    if (parallaxY < -orb.baseRadius * 1.5) {
                        orb.y = scrollY * (0.15 + orb.depth * 0.3) + h + orb.baseRadius;
                    }
                    if (parallaxY > h + orb.baseRadius * 1.5) {
                        orb.y = scrollY * (0.15 + orb.depth * 0.3) - orb.baseRadius;
                    }

                    // Déstabilisation interactive au passage de la souris
                    const dx = mouseX - orb.x;
                    const dy = (mouseY + scrollY * (0.15 + orb.depth * 0.3)) - orb.y;
                    const dist = Math.hypot(dx, dy);
                    const influenceRadius = currentRadius * 2.0;

                    if (dist < influenceRadius && dist > 0) {
                        const force = (influenceRadius - dist) / influenceRadius;
                        // Répulsion fluide avec inertie organique
                        orb.x -= (dx / dist) * force * 2.8 * orb.depth;
                        orb.y -= (dy / dist) * force * 2.8 * orb.depth;
                    }

                    // Rappel élastique très doux vers la trajectoire d'origine
                    orb.x += (orb.baseX - orb.x) * 0.008;
                    orb.y += (orb.baseY - orb.y) * 0.008;

                    const renderY = orb.y - scrollY * (0.15 + orb.depth * 0.3);

                    // Dessin d'un cercle pur, sobre et élégant (aucun contour, aucun reflet blanc artificiel)
                    graphics.circle(orb.x, renderY, currentRadius);
                    graphics.fill({ color: orb.color, alpha: currentAlpha });
                }
            } else {
                // Mode Admin : Particules constellation discrètes
                for (let i = 0; i < particles.length; i++) {
                    const p = particles[i];
                    p.x += p.vx;
                    p.y += p.vy;

                    if (p.x < 0) p.x = w;
                    if (p.x > w) p.x = 0;
                    if (p.y < 0) p.y = h;
                    if (p.y > h) p.y = 0;

                    // Déstabilisation souris
                    const dx = mouseX - p.x;
                    const dy = mouseY - p.y;
                    const dist = Math.hypot(dx, dy);
                    if (dist < 120 && dist > 0) {
                        const force = (120 - dist) / 120;
                        p.x -= (dx / dist) * force * 1.4;
                        p.y -= (dy / dist) * force * 1.4;
                    }

                    if (p.hasGlow) {
                        graphics.circle(p.x, p.y, p.radius * 2.2);
                        graphics.fill({ color: p.color, alpha: p.alpha * 0.2 });
                    }

                    graphics.circle(p.x, p.y, p.radius);
                    graphics.fill({ color: p.color, alpha: p.alpha });

                    // Lignes de connexion
                    for (let j = i + 1; j < particles.length; j++) {
                        const p2 = particles[j];
                        const dist2 = Math.hypot(p.x - p2.x, p.y - p2.y);
                        if (dist2 < 95) {
                            const lineAlpha = (1 - dist2 / 95) * 0.16;
                            graphics.moveTo(p.x, p.y);
                            graphics.lineTo(p2.x, p2.y);
                            graphics.stroke({ width: 0.8, color: 0x3a52e0, alpha: lineAlpha });
                        }
                    }
                }
            }
        });

        window.addEventListener('mousemove', handleMouseMove, { passive: true });
        window.addEventListener('mouseleave', handleMouseLeave);
        window.addEventListener('scroll', handleScroll, { passive: true });
        document.addEventListener('visibilitychange', evaluatePause);
        window.addEventListener('focus', evaluatePause);
        window.addEventListener('blur', evaluatePause);
    } catch (err) {
        console.warn('Initialisation PixiJS impossible :', err);
    }
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('mouseleave', handleMouseLeave);
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('visibilitychange', evaluatePause);
    window.removeEventListener('focus', evaluatePause);
    window.removeEventListener('blur', evaluatePause);

    if (app) {
        try {
            app.destroy(true, { children: true, texture: true });
        } catch (e) {
            // Nettoyage sécurisé
        }
        app = null;
    }
});
</script>

<template>
    <div
        class="pixi-canvas-container fixed inset-0 pointer-events-none overflow-hidden"
        :style="{ opacity: opacity, zIndex: 0 }"
        aria-hidden="true"
    >
        <div ref="containerRef" class="w-full h-full"></div>
    </div>
</template>

<style scoped>
.pixi-canvas-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
    user-select: none;
    will-change: transform;
}
:deep(canvas) {
    display: block;
    width: 100%;
    height: 100%;
}
</style>
