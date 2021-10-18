module.exports = {
  "presets": [
    [
      "@babel/preset-env",
      {
        "debug": false,
        "corejs": 3,
        "useBuiltIns": "usage"
      }
    ]
  ],
  "plugins": [
    [
      "prismjs",
      {
        "languages": [
          "markup",
          "css",
          "clike",
          "javascript",
          "ada",
          "bash",
          "basic",
          "batch",
          "brainfuck",
          "c",
          "asm6502",
          "csharp",
          "cpp",
          "fsharp",
          "clojure",
          "diff",
          "docker",
          "elixir",
          "erlang",
          "go",
          "graphql",
          "groovy",
          "haskell",
          "ini",
          "java",
          "julia",
          "kotlin",
          "latex",
          "less",
          "lisp",
          "lua",
          "markdown",
          "fortran",
          "matlab",
          "pascal",
          "perl",
          "php",
          "python",
          "r",
          "ruby",
          "rust",
          "scala",
          "sql",
          "prolog",
          "plsql",
          "twig",
          "yaml",
          "visual-basic"
        ],
        "plugins": [
          "line-numbers"
        ],
        "css": false
      }
    ]
  ],
  "env": {
    "test": {
      "presets": [
        [
          "@babel/preset-env",
          {
            "targets": {
              "node": "current"
            }
          }
        ],
        "@babel/preset-typescript"
      ],
      "plugins": [
        ["@babel/plugin-proposal-decorators", { "legacy": true }]
      ]
    }
  }
};
