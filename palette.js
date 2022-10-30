import Couleur from 'colori';


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
   */
  constructor(color, generator = () => [], options = {}) {
    this.colors = new Map(); // : Map<string, Couleur[]>. Will be a map of arrays of color nuances.

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
      let oklrch = [lightness, chroma, hue];
      
      oklrch = Couleur.toGamut('srgb', oklrch, 'oklrch');
      let rgb = Couleur.convert('oklrch', 'srgb', oklrch);

      let newColor = new Couleur(rgb);

      nuances.push(newColor);
    }
    this.colors.set(label, nuances);
  }
}