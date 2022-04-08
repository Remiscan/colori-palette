<?php namespace colori {
  require_once dirname(__DIR__, 3) . '/dist/colori.php';

  class Palette {
    public array $colors;

    function __construct(Couleur $color, callable $generator) {
      $this->colors = [];
      $colors = $generator($color);

      foreach ($colors as $color) {
        $nuances = [];
        foreach ($color->lightnesses as $lightness) {
          $rgb = Couleur::convert('oklch', 'srgb', [$lightness, $color->chroma, $color->hue]);
          $rgb = Couleur::toGamut('srgb', $rgb, 'srgb');
          $newColor = new Couleur($rgb);
          $nuances[] = $newColor;
        }
        $this->colors[] = $nuances;
      }
    }
  }
}


namespace {
  class Palette extends colori\Palette {}
}