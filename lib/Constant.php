<?php

/* Big ugly constants sit here so as not to clutter the code base. */

class Constant {
  const PARSING_ERROR_MARKER = "◼◼◼";

  /* https://en.wikipedia.org/wiki/Whitespace_character */
  const SPACES = [
    'thin' => "\u{2009}",        /* U+2009; &#8201; &thinsp; */
    'hair' => "\u{200A}",        /* U+200A; &#8202; &hairsp; */
    'zero-width' => " \u{200B}",  /* U+200B; &#8203; &NegativeMediumSpace; */
    'zwnj' => "\u{200C}",        /* U+200C; &#8204; &zwnj; */
    'zwj' => "\u{200D}",         /* U+200D; &#8205; &zwj; */
    'punctuation' => "\u{2008}", /* U+2008; &#8200; &puncsp; */
    'nobreak' => "\u{00A0}",     /* U+00A0; &#160; &nbsp;*/
    'regular' => "\u{0020}",     /* U+0020; &#32; */
    ];
  const OPENBOX = "\u{2423}";    /* U+2423; &#9251; */

  const CLEANUP_PATTERNS = [
    '/(?<!\\\\)ş/'   => 'ș',
    '/(?<!\\\\)Ş/'   => 'Ș',
    '/(?<!\\\\)ţ/'   => 'ț',
    '/(?<!\\\\)Ţ/'   => 'Ț',

    '/ ◊ /' => ' * ',  /* (U+25CA) LOZENGE ◊ */
    '/ ♦ /' => ' ** ', /* (U+2666) BLACK DIAMOND SUIT ♦ */

    // hyphens and spaces
    '/(?<!\\\\) /'   => ' ',     /* U+00A0 non-breaking space */
    '/(?<!\\\\)‑/'   => '-',     /* U+2011 non-breaking hyphen */
    '/(?<!\\\\)—/'   => '-',     /* U+2014 em dash */
    '/(?<!\\\\)­/'   => '',      /* U+00AD soft hyphen */
    '/[ \t]+/'       => ' ',     /* Leave newlines alone. Some editors like to use them in definitions. */

    // Replace a quotation mark that may look like comma
    '/(?<!\\\\)‚/'   => ',',     /* U+201A SINGLE LOW-9 QUOTATION MARK */

    // Replace the ordinal indicator with the degree sign.
    '/(?<!\\\\)º/'   =>  '°',    /* U+00BA => U+00B0 */

    "/(?<!\\\\)\r\n/" => "\n",    /* Unix newlines only */
    '/' . self::PARSING_ERROR_MARKER . '/' => '',
  ];

  const APOSTROPHE_CLEANUP_PATTERNS = [
    // Replace all kinds of single quotes and acute accents with the ASCII apostrophe.
    // Do NOT alter ′ (prime, 0x2032), which is used for foot and minute symbols.
    // Apostrophes are different from other patterns because they double as accent indicators.
    '/(?<!\\\\)´/'   => "'",     /* U+00B4 */
    '/(?<!\\\\)‘/'   => "'",     /* U+2018 */
    '/(?<!\\\\)’/'   => "'",     /* U+2019 */
  ];

  const DOUBLE_QUOTE_CLEANUP_PATTERNS = [
    // Replace all kinds of double quotes with the ASCII ones.
    // Do NOT alter ″ (double prime, 0x2033), which is used for inch and second symbols.
    '/(?<!\\\\)“/'   => '"',     /* U+201C LEFT DOUBLE QUOTATION MARK */
    '/(?<!\\\\)”/'   => '"',     /* U+201D RIGHT DOUBLE QUOTATION MARK */
    '/(?<!\\\\)„/'   => '"',     /* U+201E DOUBLE LOW-9 QUOTATION MARK */
    '/(?<!\\\\)‟/'   => '"',     /* U+201F DOUBLE HIGH-REVERSED-9 QUOTATION MARK */
  ];

  const MEANING_MENTION_PATTERN = '/([-a-zăâîșț]+)\[([0-9]+)(\*{0,2})\]/iu';

  // will use preg_replace for string values, preg_replace_callback for arrays
  const HTML_PATTERNS = [
    '/▶(.*?)◀/s' => '',                                                         // remove unwanted parts of definition
    '/(?<!\\\\)"([^"]*)"/' => '„$1”',                                           // "x" => „x” - romanian quoting style
    '/(?<!\\\\)\{{2}(.*)(?<![+])\}{2}/U' => [ 'FootnoteHtmlizer' ],             // {{footnote}}
    '/(?<!\\\\)\{-(.*)-\}/Us' => [ 'DeleteHtmlizer' ],                          // deletions {-foo-}
    '/(?<!\\\\)\{\+(.*)\+\}/Us' => [ 'InsertHtmlizer' ],                        // insertions {+foo+}
    '/(?<!\\\\)##(.*)(?<!\\\\)##/Us' => '$1',                                   // ##non-abbreviation##
    '/\{#(.*)#\}/Us' => '<span class="ambigAbbrev">$1</span>',                  // {#abbreviation#} for review
    '/(?<!\\\\)#(.*)(?<!\\\\)#/Us' => [ 'AbbrevHtmlizer' ],                     // #abbreviation#
    '/(?<!\\\\)\{~(.*)(?<!\\\\)~\}/Us' => '<span class="small-caps">$1</span>', // {~small-caps~}
    '/(?<!\\\\)~~(.*)(?<!\\\\)~~/Us' => '<span class="small-caps-l">$1</span>', // ~~small-caps-large~~
    '/(?<!\\\\)%(.*)(?<!\\\\)%/Us' => '<span class="spaced">$1</span>',         // %spaced%
    '/(?<!\\\\)@(.*)(?<!\\\\)@/Us' => '<b>$1</b>',                              // @bold@
    '/(?<!\\\\)\\$(.*)(?<!\\\\)\\$/Us' => '<i>$1</i>',                          // $italic$
    '/(?<!\\\\)\^(\d)/' => '<sup>$1</sup>',                                     // superscript ^123
    '/(?<!\\\\)\^\{([^}]*)\}/' => '<sup>$1</sup>',                              // superscript ^{a b c}
    '/(?<!\\\\)_(\d)/' => '<sub>$1</sub>',                                      // subscript _123
    '/(?<!\\\\)_\{([^}]*)\}/' => '<sub>$1</sub>',                               // superscript _{a b c}
    "/\n\ *\n/" => '<br>',                                                      // double newlines
    '/' . self::PARSING_ERROR_MARKER . '/' => '',

    // cycle CSS class {cfoo,0c}, used to highlight full-text search matches
    '/(?<!\\\\)\{c([^|}]+),(\d+)c\}/' => '<span class="fth fth$2">$1</span>',

    // |foo|bar| references
    '/(?<!\\\\)\|([^|]*)\|([^|]*)\|/' => '<a class="ref" href="/definitie/$2">$1</a>',

    // {fr: term}
    '/(?<!\\\\)\{fr:(.*)\}/' => '<a class="ref" href="https://cnrtl.fr/definition/$1"
    target="_blank" rel="nofollow" title="Clic pentru navigarea la dicționarul al Academiei franceze">$1</a>',

    // {fr-a: term}
    '/(?<!\\\\)\{fr-a:([^:]*):(.*)\}/' => '<a class="ref" href="https://www.dictionnaire-academie.fr/article/$1"
    target="_blank" rel="nofollow" title="Clic pentru navigarea la dicționarul de limbă franceză">$2</a>',

    // {en-c: term}
    '/(?<!\\\\)\{en-c:(.*)\}/' => '<a class="ref" href="https://dictionary.cambridge.org/dictionary/english/$1"
    target="_blank" rel="nofollow" title="Clic pentru navigarea la dicționarul Cambridge">$1</a>',

    // {en-w: term}
    '/(?<!\\\\)\{en-w:(.*)\}/' => '<a class="ref" href="https://www.merriam-webster.com/dictionary/$1"
    target="_blank" rel="nofollow" title="Clic pentru navigarea la dicționarul de limbă engleză">$1</a>',

    // tree mentions
    '/([-a-zăâîșț]+)\[\[([0-9]+)\]\]/iu' =>
    '<span class="treeMention" title="$2">$1</span>',

    self::MEANING_MENTION_PATTERN => [ 'MentionHtmlizer' ],              // meaning mentions
    '/(?<!\\\\)__(.*?)__/' => [ 'EmphasisHtmlizer' ],                    // __emphasis__
  ];

  const HTML_REPLACEMENTS = [
    ' - '  => ' – ',  /* U+2013 */
    ' ** ' => ' ♦ ',  /* U+2666 */
    ' * '  => ' ◊ ',  /* U+25CA */
    "\\'"  => '’',    /* U+2019 */
  ];

  const ACCENTS = [
    'accented' => [
      'á', 'Á', 'ắ', 'Ắ', 'ấ', 'Ấ', 'é', 'É', 'í', 'Í', 'î́', 'Î́',
      'ó', 'Ó', 'ö́', 'Ö́', 'ú', 'Ú', 'ǘ', 'Ǘ', 'ý', 'Ý',
    ],
    'unaccented' => [
      'a', 'A', 'ă', 'Ă', 'â', 'Â', 'e', 'E', 'i', 'I', 'î', 'Î',
      'o', 'O', 'ö', 'Ö', 'u', 'U', 'ü', 'Ü', 'y', 'Y',
    ],
    'marked' => [
      "'a", "'A", "'ă", "'Ă", "'â", "'Â", "'e", "'E", "'i", "'I", "'î", "'Î",
      "'o", "'O", "'ö", "'Ö", "'u", "'U", "'ü", "'Ü", "'y", "'Y",
    ],
  ];

  const UNICODE_SCRIPTS = [
    // script names not localized -- for editor eyes only
    'latin' => [ [0x41, 0x5a], [0x61, 0x7a], [0xc0, 0xff], [0x100, 0x17f] ],
    'grec' => [ [0x370, 0x3ff], [0x1f00, 0x1fff] ],
    'chirilic' => [ [0x400, 0x4ff], [0xa640, 0xa69f] ],
  ];

  // <from> => [<script> => <to>, ...], ...
  // If glyph $from is surrounded by two glyphs in the $script script, replace it by $to.
  const FIXABLE_UNICODE_CONFLICTS = [
    'a' => ['chirilic' => 'а'],
    'c' => ['chirilic' => 'с'],
    'e' => ['chirilic' => 'е'],
    'o' => ['chirilic' => 'о', 'grec' => 'ο'],
    'p' => ['chirilic' => 'р'],
    'x' => ['chirilic' => 'х'],
    'y' => ['chirilic' => 'у'],
  ];

  /**
   * Prefixes which should be followed by 'î', not 'â'.
   *
   * False positives:
   *   dezânoaie dezânvoltură exân
   *
   * False negatives (adding prefixes for these would also catch some words
   * with 'â`):
   *   altîncotro ciocîntors răsînțele(ge,gere,s)
   *   (pita,piț,piți)împărătuș
   *   maîtrise (French word)
   */
  const I_PREFIXES = [
    'anti', 'arhi', 'auto', 'bine', 'bio', 'bun', 'cap', 'co', 'con', 'de', 'dez',
    'di', 'ex', 'fie', 'foto', 'micro', 'mini', 'ne', 'nemai', 'ori', 'port',
    'pră', 'prea', 'pre', 'pro', 're', 'rupt', 'semi', 'sub', 'subt', 'super',
    'supra', 'supt', 'tele', 'ultra',
  ];

  /**
   * Use <b>|</b> to escape MySQL special characters so that constructs and chars like:<br/>
   * \%  - "literal percent sign",<br/>
   * _   - latex convention for subscript,<br/>
   * |   - the pipe itself,<br/>
   * remains unaffected.<br/>
   */
  const MYSQL_LIKE_ESCAPES = [
    '%' => '|%',
    '_' => '|_',
    '|' => '||',
  ];

  /**
   * Used for creating Models and for text like: select boxes, messages etc.
   */
  const BULKREPLACE_TARGETS = [
    1 => [
      'model' => 'Definition',
      'text' => 'definiții',
    ],
    2 => [
      'model' => 'Meaning',
      'text' => 'sensuri',
    ],
  ];

  // Long participles and gerunds (like dusu- and ducându-) need some special treatment.
  const LONG_VERB_INFLECTION_IDS = [ 106, 107 ];

  // Map of CSS files, JS files and dependencies for our resources.
  // Entries must be listed in the order in which they should be loaded.
  const RESOURCE_MAP = [
    'i18n' => [
      'js' => [
        'locale/%l.js',
        'i18n.js',
      ],
    ],
    'jquery' => [
      'js' => [ 'third-party/jquery-1.12.4.min.js' ],
    ],
    'jqueryui' => [
      'css' => [ 'third-party/smoothness-1.12.1/jquery-ui-1.12.1.custom.min.css' ],
      'js' => [ 'third-party/jquery-ui-1.12.1.custom.min.js' ],
    ],
    'select2' => [
      'css' => [ 'third-party/select2.min.css' ],
      'js' => [
        'third-party/select2/select2.min.js',
        'third-party/select2/i18n/ro.js',
      ],
    ],
    'select2Dev' => [
      'js' => [ 'select2Dev.js' ],
      'deps' => [ 'select2', 'sortable' ],
    ],
    'bootstrap' => [
      'css' => [
        'third-party/bootstrap.min.css',
        'third-party/bootstrap-diff.css',
      ],
      'js' => [ 'third-party/bootstrap.bundle.min.js' ],
    ],
    'tabulator' => [
      'css' => [
        'third-party/tabulator_simple.min.css',
        'tabulator.css',
      ],
      'js' => [ 'third-party/tabulator.min.js' ],
      'deps' => [ 'jquery' ],
    ],
    'sortable' => [
      'js' => [ 'sortable.js' ],
    ],
    'tablesorter' => [
      'css' => [
        'third-party/tablesorter/theme.bootstrap_4.min.css',
        'third-party/tablesorter/jquery.tablesorter.pager.min.css',
      ],
      'js' => [
        'third-party/tablesorter/jquery.tablesorter.min.js',
        'third-party/tablesorter/jquery.tablesorter.pager.min.js',
        'tablesorter.js',
      ],
    ],
    'elfinder' => [
      'css' => [
        'third-party/elfinder/css/elfinder.min.css',
        'third-party/elfinder/css/theme.css',
        'elfinder.custom.css',
      ],
      'js' => [ 'third-party/elfinder.min.js' ],
      'deps' => [ 'jqueryui' ],
    ],
    'cookie' => [
      'js' => [ 'third-party/jquery.cookie.js' ],
    ],
    'main' => [
      'css' => [ 'main.css', 'fonts.css' ],
      'js' => [ 'dex.js' ],
      'deps' => [ 'bootstrap', 'i18n', 'jquery' ],
    ],
    'loggedIn' => [
      'js' => [ 'loggedIn.js' ],
    ],
    'paradigm' => [
      'css' => [ 'paradigm.css' ],
    ],
    'jcrop' => [
      'css' => [ 'third-party/jcrop/jquery.Jcrop.min.css' ],
      'js' => [ 'third-party/jquery.Jcrop.min.js' ],
    ],
    'pixijs' => [
      'js' => [ 'third-party/pixi.min.js' ],
    ],
    'gallery' => [
      'css' => [
        'third-party/colorbox/colorbox.css',
        'gallery.css',
      ],
      'js' => [
        'third-party/colorbox/jquery.colorbox-min.js',
        'third-party/colorbox/jquery.colorbox-ro.js',
        'gallery.js',
      ],
      'deps' => [ 'sprintf' ],
    ],
    'modelDropdown' => [
      'js' => [ 'modelDropdown.js' ],
    ],
    'textComplete' => [
      'css' => [ 'third-party/jquery.textcomplete.css' ],
      'js' => [ 'third-party/jquery.textcomplete.min.js' ],
    ],
    'tinymce' => [
      'css' => [ 'tinymce.css' ],
      'js' => [
        'third-party/tinymce-5.9.2/tinymce.min.js',
        'tinymce.js',
      ],
      'deps' => [ 'cookie' ],
    ],
    'meaningTree' => [
      'css' => [ 'meaningTree.css' ],
      'js' => [ 'meaningTree.js' ],
    ],
    'editableMeaningTree' => [
      'css' => [ 'editableMeaningTree.css' ],
    ],
    'hotkeys' => [
      'js' => [
        'third-party/jquery.hotkeys.js',
        'hotkeys.js',
      ],
    ],
    'charmap' => [
      'js' => [ 'charmap.js' ],
      'deps' => [ 'cookie' ],
    ],
    'seedrandom' => [
      'js' => [ 'third-party/seedrandom.min.js' ],
    ],
    'diff' => [
      'css' => [ 'diff.css' ],
      'js' => [ 'diff.js' ],
    ],
    'datepicker' => [
      'css' => [
        'third-party/datepicker-bs5.min.css',
        'datepicker.css',
      ],
      'js' => [
        'third-party/datepicker-full.min.js',
        'third-party/datepicker-ro.js',
      ],
    ],
    'frequentObjects' => [
      'css' => [ 'frequentObjects.css' ],
      'js' => [ 'frequentObjects.js' ],
    ],
    'bootstrap-select' => [
      'css' => [ 'third-party/bootstrap-select.min.css' ],
      'js' => [ 'third-party/bootstrap-select.min.js' ],
      'deps' => [ 'bootstrap' ],
    ],
    'admin' => [
      'css' => [ 'admin.css' ],
      'js' => [ 'admin.js' ],
      'deps' => [ 'hotkeys', 'select2' ],
    ],
    'sprintf' => [
      'js' => [ 'third-party/sprintf.min.js' ],
    ],
    'scrollTop' => [
      'css' => [ 'scrollTop.css' ],
      'js' => [ 'scrollTop.js' ],
    ],
  ];

}
