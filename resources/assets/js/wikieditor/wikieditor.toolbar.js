/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

var toolbarSet = [


	new Button({
		name: 'Bold',
		openWith: "**",
		closeWith: "**",
		className: 'wiki-bold',
		title: 'Pogrubienie',
		fa: "fa-bold"
	}),
	new Button({
		name: 'Italic',
		openWith: "//",
		closeWith: "//",
		className: 'wiki-italic',
		title: 'Kursywa',
		fa: "fa-italic"
	}),
	new Button({
		name: 'Underline',
		openWith: '__',
		closeWith: '__',
		className: 'wiki-underline',
		title: 'Podkreślenie',
		fa: "fa-underline"
	}),
	new Button({
		name: 'Strike',
		openWith: '<del>',
		closeWith: '</del>',
		className: 'wiki-strike',
		title: 'Przekreślenie',
		fa: "fa-strikethrough"
	}),
	new Button({
		name: 'Teletype',
		openWith: "''",
		closeWith: "''",
		className: 'wiki-teletype',
		title: 'Tekst o stałych odstępach',
		fa: "fa-text-width"
	}),
	new Separator(),
	new Button({
		name: 'Heading2',
		openWith: "\n== ",
		closeWith: " ==\n",
		className: 'wiki-heading2',
		title: 'Nagłówek h2',
		text: 'Nagłówek h2',
		fa: "fa-header"
	}),
	new Button({
		name: 'Heading3',
		openWith: "\n=== ",
		closeWith: " ===\n",
		className: 'wiki-heading3',
		title: 'Nagłówek h3',
		text: 'Nagłówek h3',
		fa: ""
	}),
	new Button({
		name: 'Heading4',
		openWith: "\n==== ",
		closeWith: " ====\n",
		className: 'wiki-heading4',
		title: 'Nagłówek h4',
		text: 'Nagłówek h4',
		fa: ""
	}),
	new Button({
		name: 'Heading5',
		openWith: "\n===== ",
		closeWith: " =====\n",
		className: 'wiki-heading5',
		title: 'Nagłówek h5',
		text: 'Nagłówek h5',
		fa: ""
	}),
	new Button({
		name: 'Heading6',
		openWith: "\n====== ",
		closeWith: " ======\n",
		className: 'wiki-heading6',
		title: 'Nagłówek h6',
		text: 'Nagłówek h6',
		fa: ""
	}),
	new Separator(),
	new Button({
		name: 'List numbers',
		className: 'wiki-ul',
		openWith: "\n# ",
		closeWith: '',
		title: 'Lista numerowana',
		text: ' ',
		fa: "fa-list-ul"
	}),
	new Button({
		name: 'List bullets',
		className: 'wiki-ol',
		openWith: "\n* ",
		closeWith: '',
		title: 'Lista wypunktowana',
		text: ' ',
		fa: "fa-list-ol"
	}),
	new Separator(),
	new Button({
		name: 'Sub',
		className: 'wiki-sub',
		openWith: ',,',
		closeWith: ',,',
		title: 'Indeks dolny',
		text: ' ',
		fa: "fa-subscript"
	}),
	new Button({
		name: 'Sup',
		className: 'wiki-sup',
		openWith: '^',
		closeWith: '^',
		title: 'Indeks górny',
		text: ' ',
		fa: "fa-superscript"
	}),
	new Separator(),
	new Button({
		name: 'Image',
		className: 'wiki-image',
		openWith: "{{Image:",
		closeWith: '}}',
		title: 'Obrazek',
		text: 'foo.jpg',
		fa: "fa-image"
	}),
	new Button({
		name: 'File',
		className: 'wiki-file',
		openWith: '{{File:',
		closeWith: '}}',
		title: 'Załącznik',
		text: 'foo.zip',
		fa: "fa-file-o"
	}),
	new Button({
		name: 'Anchor',
		className: 'wiki-anchor',
		openWith: '[[',
		closeWith: ']]',
		title: 'Odnośnik do wewnętrznego dokumentu',
		text: 'Tytuł/ścieżka dokumentu',
		fa: "fa-link"
	}),
	new Button({
		name: 'Table',
		className: 'wiki-table',
		openWith: "\n||=Nagłówek 1||Nagłówek 2\n||Kolumna 1||Kolumna 2",
		closeWith: '',
		title: 'Tabela',
		text: ' ',
		fa: "fa-table"
	}),
	new Separator(),
	new Button({
		name: 'Code',
		className: 'wiki-code',
		openWith: '<code>',
		closeWith: '</code>',
		title: 'Kod źródłowy',
		text: ' ',
		fa: "fa-code"
	}),
	new Button({
		name: 'Delphi',
		className: 'wiki-delphi',
		openWith: '<code=delphi>',
		closeWith: '</code>',
		title: 'Kod źródłowy Delphi',
		text: ' ',
		fa: ""
	}),
	new Button({
		name: 'PHP',
		className: 'wiki-php',
		openWith: '<code=php>',
		closeWith: '</code>',
		title: 'Kod źródłowy PHP',
		text: ' ',
		fa: ""
	}),
	new Button({
		name: 'C',
		className: 'wiki-c',
		openWith: '<code=c>',
		closeWith: '</code>',
		title: 'Kod źródłowy C',
		text: ' ',
		fa: ""
	}),
	new Button({
		name: 'Cplusplus',
		className: 'wiki-cpp',
		openWith: '<code=cpp>',
		closeWith: '</code>',
		title: 'Kod źródłowy C++',
		text: ' ',
		fa: ""
	}),
	new Button({
		name: 'Csharp',
		className: 'wiki-csharp',
		openWith: '<code=csharp>',
		closeWith: '</code>',
		title: 'Kod źródłowy C#',
		text: ' ',
		fa: ""
	}),
	new Button({
		name: 'SQL',
		className: 'wiki-sql',
		openWith: '<code=sql>',
		closeWith: '</code>',
		title: 'Kod źródłowy SQL',
		text: ' ',
		fa: ""
	}),
	$highlightBox = new ComboBox({
		name: 'highlight',
		title: 'Wstaw znacznik kolorowania składni',
		text: ' ',
		items: [

		        { id: 0, value: 'Kolorowanie składni' },
		        { id: 'asm', value: 'Assembler' },
		        { id: 'bash', value: 'Bash' },
		        { id: 'c', value: 'C' },
				{ id: 'clojure', value: 'Clojure' },
		        { id: 'cpp', value: 'C++' },
		        { id: 'csharp', value: 'C#' },
		        { id: 'css', value: 'CSS' },
		        { id: 'delphi', value: 'Delphi' },
		        { id: 'diff', value: 'Diff' },
		        { id: 'fortan', value: 'Fortran' },
		        { id: 'html', value: 'HTML' },
		        { id: 'ini', value: 'INI' },
		        { id: 'java', value: 'Java' },
		        { id: 'javascript', value: 'JavaScript' },
		        { id: 'jquery', value: 'jQuery' },
				{ id: 'lisp', value: 'Lisp' },
		        { id: 'pascal', value: 'Pascal' },
		        { id: 'perl', value: 'Perl' },
		        { id: 'php', value: 'PHP' },
		        { id: 'plsql', value: 'PL/SQL' },
		        { id: 'python', value: 'Python' },
		        { id: 'ruby', value: 'Ruby' },
		        { id: 'sql', value: 'SQL' },
		        { id: 'vbnet', value: 'Visual Basic.NET' },
		        { id: 'xml', value: 'XML' }
		]
	}),
	new Separator(),
	new Help({
		name: 'help',
		className: 'wiki-help-button',
		title: 'Pomoc w formatowaniu tekstu',
		text: ' ',
		fa: "fa-question"
	})
];