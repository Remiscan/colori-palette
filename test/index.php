<!doctype html>

<!-- ▼ Cache-busted files -->
<!--<?php versionizeStart(); ?>-->

<!-- Import map -->
<script defer src="/_common/polyfills/es-module-shims.js"></script>
<script type="importmap">
{
  "imports": {
    "colori": "/colori/lib/dist/colori.min.js",
    "palette": "/colori/palette/palette.js"
  }
}
</script>

<link rel="stylesheet" href="./styles.css">

<!--<?php versionizeEnd(__DIR__); ?>-->

<style>
  .palette {
    display: flex;
    flex-direction: row;
  }

  .palette>div {
    width: 4em;
    height: 3em;
    background-color: var(--color);
    display: grid;
    place-content: center;
  }
</style>

<h1>Testing colori's Palette generation</h1>

<p>
  <label for="hue">Hue</label>
  <input type="range" id="hue" min="0" max="360" step="1" value="62">
  <span id="hue-value"></span>
</p>
<p>
  <label for="chroma">Chroma</label>
  <input type="range" id="chroma" min="0" max="0.32" step="0.001" value="0.13">
  <span id="chroma-value"></span>
</p>

<script type="module">
  import Couleur from 'colori';
  import Palette from 'palette';



  // Monet-like palette

  const monetGenerator = function(color) {
    const hue = color.okh;

    return {
      lightnesses: [1, .99, .95, .9, .8, .7, .6, .5, .4, .3, .2, .1, 0],
      colors: [
        { label: 'primary', chroma: .1305, hue: hue },
        { label: 'secondary', chroma: .0357, hue: hue },
        { label: 'tertiary', chroma: .0605, hue: hue + 60},
        { label: 'error', chroma: .1783, hue: 28 },
        { label: 'neutral', chroma: .0058, hue: hue },
        { label: 'neutral-variant', chroma: .0178, hue: hue }
      ]
    };
  };

  class MaterialLikePalette extends Palette {
    constructor(hue) {
      const color = new Couleur(`color(oklrch 0.5 0.1305 ${hue})`);
      super(color, monetGenerator);
    }
  }



  // Contrasted palette

  const contrastedGenerator = function(color) {
    /*const grey = new Couleur('color(oklab .5 0 0)');
    const light = [];
    const dark = [];
    const contrasts = [65, 75, 85, 95, 100, 105];
    for (const i of contrasts) {
      light.push(grey.improveContrast('black', i, { lower: true, as: 'background' }));
      dark.push(grey.improveContrast('white', i, { lower: true, as: 'background' }));
    }
    const lightnesses = [...light.reverse(), ...dark].map(c => c.okl);
    console.log(JSON.stringify(lightnesses));*/

    const [x, chroma, hue] = color.valuesTo('oklrch');

    return {
      // Lightnesses computed with the previous commented code
      lightnesses: [1, .99, .95, .9, .8, .7, .6, .5, .4, .3, .2, .1, 0],
      colors: [
        { label: 'neutral', hue, chroma: 0 },
        { label: 'accent1', hue, chroma: chroma / 6 },
        { label: 'accent2', hue, chroma: chroma / 3 },
        { label: 'accent3', hue, chroma: chroma }
      ]
    };
  };

  // improveContrast after generating each color
  // (way slower, more precise, maybe not worth it)
  // (contrasts may still be very slightly lower than requested because of the clamping to srgb in Palette constructor)
  /*const contrastedGenerator2 = function(color) {
    const [x, chroma, hue] = color.valuesTo('oklch');
    const contrasts = [65, 75, 85, 95, 100, 105];
    const chromas = [0, chroma / 6, chroma / 3, chroma];
    return chromas.map((ch, k) => {
      const base = (new Couleur(`color(oklch .5 ${ch} ${hue})`)).toGamut('srgb');
      const light = [];
      const dark = [];
      for (const c of contrasts) {
        light.push(base.improveContrast('black', c, { lower: true, as: 'background' }));
        dark.push(base.improveContrast('white', c, { lower: true, as: 'background' }));
      }
      const lightnesses = [...light.reverse(), ...dark].map(e => e.okl);
      return {
        lightnesses,
        chroma: ch,
        hue
      };
    });
  };*/

  class ContrastedPalette extends Palette {
    constructor(hue, chroma) {
      const color = new Couleur(`color(oklrch 0.5 ${chroma} ${hue})`);
      super(color, contrastedGenerator);
    }
  }



  const classes = [ 'MaterialLikePalette', 'ContrastedPalette' ];

  function makePalet(className, h, c) {
    const hue = parseFloat(h);
    const chroma = parseFloat(c);
    return eval(`new ${className}(${hue}, ${chroma})`);
  }



  function updatePalets(hue, chroma) {
    document.querySelector('#hue-value').innerHTML = hue;
    document.querySelector('#chroma-value').innerHTML = chroma;

    const containers = [...document.querySelectorAll('.paletteContainer')];
    for (const container of containers) {
      container.innerHTML = '';

      let pal = makePalet(container.dataset.type, hue, chroma);

      for (const [label, nuances] of pal.colors) {
        let html = `<div class="palette" data-label="${label}">
          <div>${label}</div>`;
        for (const color of nuances) {
          const textColor = color.bestColorScheme() === 'dark' ? 'white' : 'black';
          const contrast = Couleur.contrast(textColor, color, { method: 'apca' });
          const otherColor = textColor === 'white' ? 'black' : 'white';
          const otherContrast = Couleur.contrast(otherColor, color, { method: 'apca' });
          const cBlack = Couleur.contrast('black', color, { method: 'apca' });
          const cWhite = Couleur.contrast('white', color, { method: 'apca' });
          html += `<div style="--color: ${color.hsl}; color: ${textColor}; display: grid; place-items: center; font-family: system-ui;"
                        data-values="${color.values.join(' ; ')}"
                        data-rgb="${color.rgb}"
                        data-oklch="${color.valuesTo('oklch').join(' ; ')}">
            <span style="color: ${textColor}; font-weight: 600;">${Math.round(100 * contrast) / 100}</span>
            <span style="color: ${otherColor}; font-size: .8em">${Math.round(100 * otherContrast) / 100}</span>
          </div>`;
        }
        html += `</div>`;
        container.innerHTML += html;
      }
    }
  }



  function initPalets() {
    for (const c of classes) {
      document.body.innerHTML += `
        <h2>Palette: ${c}</h2>
        <div class="paletteContainer" data-type="${c}"></div>
      `;
    }
    const inputs = [document.querySelector('#hue'), document.querySelector('#chroma')];
    for (const input of inputs) {
      input.addEventListener('change', event => {
        updatePalets(inputs[0].value, inputs[1].value);
      });
    }
    updatePalets(inputs[0].value, inputs[1].value);
  }
  initPalets();
</script>