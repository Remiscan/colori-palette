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
   * @param {object} options
   * @param {boolean} options.recursivelyForceLightness - Whether to reapply the desired OKLightness to each color after it's been gamut mapped to SRGB, again and again until the color is in SRGB space AND has the requested lightness.
   */
  constructor(color, generator = () => [], { recursivelyForceLightness = false } = {}) {
    this.colors = []; // : Couleur[][]. Will be an array of arrays of color nuances.
    const colors = generator(color);

    // Create the nuances of each color.
    for (const color of colors) {
      const nuances = []; //: Couleur[]
      for (const lightness of color.lightnesses) {
        let lightnessDiff = +Infinity;
        let oklch = [lightness, color.chroma, color.hue];
        if (recursivelyForceLightness) {
          while (lightnessDiff > 0.001) {
            oklch = Couleur.toGamut('srgb', oklch, 'oklch');
            lightnessDiff = Math.abs(lightness - oklch[0]);
            oklch[0] = lightness;
          }
        }
        let rgb = Couleur.convert('oklch', 'srgb', oklch);
        rgb = Couleur.toGamut('srgb', rgb, 'srgb');
        let newColor = new Couleur(rgb);
        nuances.push(newColor);
      }
      this.colors.push(nuances);
    }
  }
}