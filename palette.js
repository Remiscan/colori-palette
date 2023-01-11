// @ts-check
import Couleur from 'colori';



/**
 * @typedef { { label: string, hue: number, chroma: number } } colorData
 * @typedef { { lightnesses: number[], colors: colorData[] } } paletteData
 */



/**
 * @class A color palette generated with Colori.
 * @property {Map<string, Couleur[]>} colors
 */
export default class Palette {
  /**
   * Creates a color palette from a color.
   * @param {Couleur} color - The color from which the palette will be derived.
   * @param {(...args: any[]) => paletteData} generator - A function that generates data about each key color in the palette.
   * @param {object} options
   */
  constructor(color, generator, options = {}) {
    this.colors = new Map(); // : Map<string, Couleur[]>. Will be a map of arrays of color nuances.

    const data = generator(color);
    this.lightnesses = data.lightnesses; // Color lightnesses in OKLrCH format.
    
    for (const colorData of data.colors) {
      this.addKeyColor(colorData);
    }
  }


  /**
   * Adds a color to the palette.
   * @param {colorData} arguments
   */
  addKeyColor({ label, hue, chroma }) {
    const nulls = [];
    if (label == null)  nulls.push('label');
    if (hue == null)    nulls.push('hue');
    if (chroma == null) nulls.push('chroma');
    if (nulls.length > 0) throw `Can't add key color: ${nulls.join(', ')} missing`;

    const nuances = []; //: Couleur[]
    for (const lightness of this.lightnesses) {
      let oklrch = [lightness, chroma, hue];
      
      let rgb = Couleur.convert('oklrch', 'srgb', oklrch);
      rgb = Couleur.valuesToGamut('srgb', rgb);

      let newColor = new Couleur(rgb);

      nuances.push(newColor);
    }
    this.colors.set(label, nuances);
  }


  /** Returns css variables with all the color hex expressions. */
  toCSS() {
    let css = ``;
    for (const [label, colors] of this.colors) {
      for (let k = 0; k < colors.length; k++) {
        const color = colors[k];
        const lightness = this.lightnesses[k];
        css += `--${label}-${String(100 * lightness).replace(/[^0-9]/g, '_')}:${color.hex};`;
      }
    }
    return css;
  }
}