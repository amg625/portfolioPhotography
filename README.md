# 📸 Portfolio Fotográfico — Guillermo Alvarez

Portfolio personal de fotografía de retrato y lifestyle en CDMX.

## Estructura del proyecto

```
portfolio-foto/
├── index.html              ← Página principal
├── mail.php                ← Backend formulario (sube a Hostinger)
├── README.md
└── assets/
    ├── css/style.css
    ├── js/main.js
    └── img/
        ├── hero/           ← Foto de portada (hero.jpg)
        ├── about/          ← Tu foto (perfil.jpg)
        └── portfolio/
            ├── retrato/    ← foto1.jpg, foto2.jpg ...
            ├── lifestyle/
            └── urbana/
```

## Configuración rápida

### 1. Tus fotos
Agrega tus imágenes en `assets/img/`. Formatos recomendados: `.jpg` o `.webp`.
- Hero: apaisada, mínimo 1920px ancho
- Portafolio: cualquier relación de aspecto, mínimo 800px
- Perfil: vertical preferible, mínimo 600x800

### 2. Personalización
Abre `index.html` y busca los comentarios con `<!-- Reemplaza -->` y cambia:
- Tu nombre (3 lugares)
- Tu email, WhatsApp, Instagram
- URL de Pixieset
- Precios de servicios

### 3. Formulario
1. Sube `mail.php` a tu hosting en Hostinger
2. Edita las credenciales de BD en `mail.php` (líneas 50-53)
3. Cambia `FORM_ENDPOINT` en `assets/js/main.js` (línea 10) por la URL de tu mail.php

### 4. GitHub Pages (opcional)
El site funciona 100% estático en GitHub Pages **sin** `mail.php`.
Para el formulario necesitas un servidor PHP (Hostinger).

**Para subir a GitHub:**
```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/tu-usuario/portfolio-foto.git
git push -u origin main
```

## Agregar más fotos al portafolio

En `index.html`, duplica este bloque dentro de `#portfolioGrid`:

```html
<div class="portfolio-item" data-cat="CATEGORÍA" data-full="assets/img/portfolio/CATEGORÍA/FOTO.jpg">
  <img src="assets/img/portfolio/CATEGORÍA/FOTO.jpg" alt="Descripción" loading="lazy"/>
  <div class="portfolio-overlay">
    <span>CATEGORÍA</span>
    <button class="zoom-btn" aria-label="Ver foto">↗</button>
  </div>
</div>
```

Categorías disponibles: `retrato`, `lifestyle`, `urbana`

## Tech stack
- HTML5 semántico
- CSS3 puro (variables, grid, flexbox, animaciones)
- JS vanilla (sin frameworks)
- PHP para el backend del formulario
- MySQL para guardar contactos