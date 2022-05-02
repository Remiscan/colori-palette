import Couleur from '../dist/colori.js';


/**
 * @typedef { { label: string, hue: number, chroma: number } } colorData
 * @typedef { { lightnesses: number[], colors: colorData[] } } paletteData
 */



export default class Palette {
  /**
   * Creates a color palette from a color.
   * @param {Couleur} color - The color from which the palette will be derived.
   * @param {function(Couleur): paletteData} generator - A function that generates data about each key color in the palette.
   * @param {object} options
   * @param {boolean} options.recursivelyForceLightness - Whether to reapply the desired OKLightness to each color after it's been gamut mapped to SRGB, again and again until the color is in SRGB space AND has the requested lightness.
   */
  constructor(color, generator = () => [], options = {}) {
    this.colors = new Map(); // : Map<string, Couleur[]>. Will be a map of arrays of color nuances.
    this.options = {
      recursivelyForceLightness: options.recursivelyForceLightness ?? false
    };

    const data = generator(color);
    this.lightnesses = data.lightnesses;
    
    for (const colorData of data.colors) {
      this.addKeyColor(colorData);
    }
  }


  addKeyColor({ label, hue, chroma }) {
    const nulls = [];
    if (label == null)  nulls.push('label');
    if (hue == null)    nulls.push('hue');
    if (chroma == null) nulls.push('chroma');
    if (nulls.length > 0) throw `Can't add key color: ${nulls.join(', ')} missing`;

    const nuances = []; //: Couleur[]
    for (const lightness of this.lightnesses) {
      let lightnessDiff = +Infinity;
      let oklch = [lightness, chroma, hue];

      if (this.options.recursivelyForceLightness) {
        while (lightnessDiff > 0.001) {
          oklch = Couleur.toGamut('srgb', oklch, 'oklch');
          lightnessDiff = Math.abs(lightness - oklch[0]);
          oklch[0] = lightness;
        }
        oklch[2] = hue;
      }

      let rgb = Couleur.convert('oklch', 'srgb', oklch);
      rgb = Couleur.toGamut('srgb', rgb, 'srgb');
      let newColor = new Couleur(rgb);

      nuances.push(newColor);
    }
    this.colors.set(label, nuances);
  }
}