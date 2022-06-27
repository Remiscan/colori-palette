<!doctype html>

<!-- ▼ Fichiers cache-busted grâce à PHP -->
<!--<?php ob_start();?>-->

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

<!--<?php $imports = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/_common/php/versionize-files.php';
echo versionizeFiles($imports, __DIR__); ?>-->

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
    const [x, chroma, hue] = color.valuesTo('oklch');
    const chromas = [
      chroma / 12,
      chroma / 6,
      chroma,
      chroma / 3,
      chroma * 2 / 3
    ];

    let lightnesses;

    /* OK lightnesses from https://github.com/kdrag0n/android12-extensions
    lightnesses = [1, .9880873963836093, .9551400440214246, .9127904082618294, .8265622041716898, .7412252673769428, .653350946076347, .5624050605208273, .48193149058901036, .39417829080418526, .3091856317280812, .22212874192541768, 0];
    */

    /*
    fromCIEtoOKlightnesses: {
      // CIE lightnesses from Google's Material Design 3 guidelines
      const CIElightnesses = [1, .99, .95, .9, .8, .7, .6, .5, .4, .3, .2, .1, 0];
      const OKlightnesses = [];
      for (const ciel of CIElightnesses) {
        const grey = new Couleur(`lch(${ciel * 100}% 0 0)`);
        OKlightnesses.push(grey.okl);
      }
      lightnesses = OKlightnesses;
    }
    */

    // Generated by fromCIEtoOKlightnesses code block
    lightnesses = [1, 0.9913761341063112, 0.956893485963483, 0.9137901757849478, 0.8275835554278771, 0.7413769350708067, 0.6551703147137362, 0.5689636943566656, 0.4827570739995951, 0.39655045364252445, 0.310343833285454, 0.2241372129283834, 0];

    return {
      lightnesses,
      colors: [
        { label: 'neutral1', hue, chroma: chroma / 12 },
        { label: 'neutral2', hue, chroma: chroma / 6 },
        { label: 'accent1', hue, chroma },
        { label: 'accent2', hue, chroma: chroma / 3 },
        { label: 'accent3', hue: hue + 60, chroma: chroma * 2 / 3 }
      ]
    };
  };

  class MaterialLikePalette extends Palette {
    constructor(hue) {
      const color = new Couleur(`oklch(50% 0.1328123146401862 ${hue})`);
      super(color, monetGenerator, { recursivelyForceLightness: true });
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

    const [x, chroma, hue] = color.valuesTo('oklch');

    return {
      // Lightnesses computed with the previous commented code
      lightnesses: [0.9948730403485463, 0.969787591129796, 0.9442748958172962, 0.8917236262860462, 0.8368530208172963, 0.7792968684735463, 0.6503906184735461, 0.5809936458172961, 0.5017700130047963, 0.4064331001636162, 0.3482665970166082, 0.2636718715583154],
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
      const color = new Couleur(`oklch(50% ${chroma} ${hue})`);
      super(color, contrastedGenerator, { recursivelyForceLightness: true });
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