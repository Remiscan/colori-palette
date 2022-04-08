// @ts-ignore
import Couleur from '../../dist/colori.min.js';
export default class Palette {
    colors = []; // Will be an array of arrays of color nuances.
    /**
     * Creates a color palet from a hue.
     * @param color The color from which the palet will be derived.
     * @param generator A function that generates an array of { lightnesses, chroma, hue } objects (values in OKLAB color space).
     * @param options
     * @param options.clampSpace Color space to which the generated colors will be clamped. Null to disable clamping.
     */
    constructor(color, generator = () => [], { clampSpace = 'srgb' } = {}) {
        const colors = generator(color);
        // Create the nuances of each color.
        for (const color of colors) {
            const nuances = [];
            for (const lightness of color.lightnesses) {
                let rgb = Couleur.convert('oklch', 'srgb', [lightness, color.chroma, color.hue]);
                if (clampSpace != null)
                    rgb = Couleur.toGamut(clampSpace, rgb);
                const newColor = new Couleur(`color(srgb ${rgb.join(' ')})`);
                nuances.push(newColor);
            }
            this.colors.push(nuances);
        }
    }
}
