import Couleur from '../dist/colori.js';


/**
 * An array of lightness[], chroma, hue
 * @typedef {Array<{ lightnesses: number[], chroma: number, hue: number}>} nuancesArray
 */



export default class Palette {
  /**
   * Creates a color palet from a hue.
   * @param {Couleur} color - The color from which the palet will be derived.
   * @param {function(Couleur): nuancesArray} generator - A function that generates an array of { lightnesses, chroma, hue } objects (values in OKLAB color space).
   * @param options
   * @param {string} options.clampSpace - Color space to which the generated colors will be clamped. Null to disable clamping.
   */
  constructor(color, generator = () => [], { clampSpace = 'srgb' } = {}) {
    this.colors = []; // : Couleur[][]. Will be an array of arrays of color nuances.
    const colors = generator(color);

    // Create the nuances of each color.
    for (const color of colors) {
      const nuances = []; //: Couleur[]
      for (const lightness of color.lightnesses) {
        let rgb = Couleur.convert('oklch', 'srgb', [lightness, color.chroma, color.hue]);
        if (clampSpace != null) rgb = Couleur.toGamut(clampSpace, rgb);
        const newColor = new Couleur(`color(srgb ${rgb.join(' ')})`);
        nuances.push(newColor);
      }
      this.colors.push(nuances);
    }
  }
}