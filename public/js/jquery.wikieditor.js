/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

function Component(attributes)
{
	this.component = 'wiki-component';

	this.toString = function()
	{
		return this.onRender();
	};
}

function Button(attributes)
{
	this.inheritFrom = Component;
	this.inheritFrom();

	this.component = 'wiki-button';

	var textarea = null;
	var self = this;

	this.property =
	{
		name:				'',
		className:			'',
		openWith:			'',
		closeWith:			'',
		title:				'',
		text:				''
	};
	this.property = $.extend(this.property, attributes);

	this.onClick = function()
	{
		textarea.insertAtCaret(self.property.openWith, self.property.closeWith, self.property.text ? self.property.text : 'foo');
	};

	this.onRender = function(parent)
	{
		textarea = parent;

		var button = $('<a title="' + this.property.title + '" class="' + this.property.className + '"><i class="fa fa-fw ' + this.property.fa + '"></i></a>');
		button.click(this.onClick);

		return button;
	};
}

function Help(attributes)
{
	this.inheritFrom = Component;
	this.inheritFrom();

	this.component = 'wiki-button help';

	var textarea = null;
	var self = this;

	this.property =
	{
		name:				'',
		className:			'',
		openWith:			'',
		closeWith:			'',
		title:				'',
		text:				''
	};
	this.property = $.extend(this.property, attributes);

	var help = "<h2>Pogrubienie, kursywa...</h2>\
\
<p>Możesz używać pogrubienia czy kursywy, aby usprawnić czytelność tekstu: <kbd>**to jest pogrubienie**, a to\
	//kursywa//</kbd>. Możesz również używać <kbd>__podkreślenia__</kbd>.\
</p>\
\
<h2>Kod źródłowy</h2>\
\
<p>Wszelkie jednolinijkowe instrukcje języka programowania (fragmenty kodu) powinny być zawarte pomiędzy obrócone\
	apostrofy\
	lub podwójny cudzysłów, czyli: <kbd>`kod instrukcji języka programowania` lub ''instrukcje języka\
		programowania''</kbd>.</p>\
\
<p>Znacznik <kbd>&lt;code&gt;</kbd> umożliwia kolorowanie większych fragmentów kodu. Możemy nadać nazwę języka\
	programowania,\
	aby system użył konkretnych ustawień kolorowania składnii:\
	<br/><br/>\
	<kbd>\
		&lt;code=javascript&gt;<br/>\
		&nbsp;&nbsp;document.write('Hello World');<br/>\
		&lt;/code&gt;<br/>\
	</kbd></p>\
\
<h2>Nagłówki</h2>\
\
<p>\
	<kbd>== Nagłówek 2 ==</kbd><br/>\
	<kbd>=== Nagłówek 3 ===</kbd><br/>\
	<kbd>==== Nagłówek 4 ====</kbd>\
</p>\
\
<h2>Wypunktowanie i numerowanie</h2>\
\
<p>Możliwe jest tworzenie listy numerowanych oraz wypunktowanych. Wystarczy, że pierwszym znakiem linii będzie\
	<kbd>*</kbd> lub <kbd>#</kbd></p>\
\
<p>\
	<kbd># Lista numerowana</kbd><br/>\
	<kbd># Lista numerowana</kbd><br/>\
</p>\
<p></p>\
<p>\
	<kbd>* Lista wypunktowana</kbd><br/>\
	<kbd>* Lista wypunktowana</kbd><br/>\
	<kbd>** Lista wypunktowana (drugi poziom)</kbd><br/>\
</p>\
\
<h2>Linki</h2>\
\
<p>URL umieszczony w tekście zostanie przez system automatycznie wykryty i zamieniony na znacznik <kbd>\
	&lt;a&gt;</kbd>.<br/>\
	Jeżeli chcesz, możesz samodzielnie sformatować link: <kbd>&lt;a href=\"http://4programmers.net\">kliknij tutaj&lt;/a&gt;</kbd>\
</p>\
\
<p>Możesz umieścić odnośnik do wewnętrznej podstrony, używając następującej składnii: <kbd>[[Delphi/Kompendium]]</kbd>\
	lub <kbd>[[Delphi/Kompendium|kliknij, aby przejść do kompendium]]</kbd></p>\
\
<h2>Znaczniki HTML</h2>\
\
<p>Dozwolone jest używanie podstawowych znaczników HTML: &lt;a&gt;, &lt;b&gt;, &lt;i&gt;, &lt;del&gt;, &lt;strong&gt;,\
	&lt;tt&gt;, &lt;dfn&gt;, &lt;ins&gt;, &lt;pre&gt;, &lt;blockquote&gt;, &lt;hr&gt;, &lt;sub&gt;, &lt;sup&gt;, &lt;img&gt;</p>\
\
<h2>Indeks górny oraz dolny</h2>\
\
<p>Przykład: wpisując <kbd>m,,2,, i m^2^</kbd> otrzymasz: m<sub>2</sub> i m<sup>2</sup>.</p>\
\
<h2>Składnia Tex</h2>\
\
<p><kbd>&lt;tex&gt;arcctg(x) = argtan(\\frac{1}{x}) = arcsin(\frac{1}{\\sqrt{1+x^2}})&lt;/tex&gt;</kbd></p>\
\
<h2>Brak formatowania</h2>\
\
<p>Jeżeli nie chcesz, aby dany fragment tekstu był interpretowany przez system, umieść go pomiędzy znaczniki <kbd>&lt;plain&gt;</kbd>.\
	Wewnątrz takiego znacznika, nie są interpretowane znaczniki HTML ani jakiekolwiek formatowanie tekstu.</p>\
\
<p>Dodatkowo znaki nowej linii tworzony podwójny Enter.</p>";

	this.onClick = function()
	{
		$('.wiki-help').toggle();
	};

	this.onRender = function(parent)
	{
		$('<div class="wiki-help">' + help + '</div>').insertAfter('.wiki-toolbar');
		textarea = parent;

		var button = $('<a title="' + this.property.title + '" class="' + this.property.className + '"><i class="fa fa-fw ' + this.property.fa + '"></i></a>');
		button.click(this.onClick);

		return button;
	};
}

function Separator(attributes)
{
	this.inheritFrom = Component;
	this.inheritFrom();

	this.component = 'wiki-separator';

	this.property =
	{
		className:			''
	};
	this.property = $.extend(this.property, attributes);

	this.onRender = function()
	{
		return $('<a class="' + this.property.className + '"></a>');
	};
}

function ComboBox(attributes)
{
	this.inheritFrom = Component;
	this.inheritFrom();

	this.component = 'wiki-combobox';
	var textarea = null;

	this.onChange = function()
	{
		if ($(this).val() != 0)
		{
			textarea.insertAtCaret('<code=' + $(this).val() + '>', '</code>', '');
			$(this).val(0);
		}
	};

	this.property =
	{
		name:				'',
		className:			'',
		openWith:			'',
		closeWith:			'',
		title:				'',
		text:				''
	};
	this.property = $.extend(this.property, attributes);

	this.onRender = function(parent)
	{
		textarea = parent;
		comboBox = $('<select></select>').attr('name', this.property.name);

		$.each(this.property.items, function()
		{
			comboBox.append('<option value="' + this.id + '">' + this.value + '</option>');
		});
		comboBox.change(this.onChange);

		return comboBox;
	};
}


(function($)
{
	$.fn.wikiEditor = function(componentSet)
	{
		//var path;

		//$('script').each(function(i, element)
		//{
		//	path = element.src.match(/^(.+)\/jquery.wikieditor.js/);
        //
		//	if (path)
		//	{
		//		$('body').append('<link rel="stylesheet" ' +
		//								'href="' + path[1] + '/wikieditor.css" ' +
		//								'type="text/css" />'
		//						);
        //
		//		return false;
		//	}
		//});

		//if (!jQuery.isArray(componentSet))
		//{
		//	$('body').append('<script type="text/javascript" src="' + path[1] + '/wikieditor.toolbar.js"></script>');
		//}
		//else
		//{
		//	toolbarSet = componentSet;
		//}

		return this.each(function()
		{
			var textarea = $(this);
			//textarea.removeAttr('style');

			$(this).wrap('<div class="wiki-container"></div>');
			var toolbar = $('<div class="wiki-toolbar"></div>').insertBefore($(this));
			$(this).wrap('<div class="wiki-editor"></div>');

			var ul = $('<ul></ul>');

			$.each(toolbarSet, function()
			{
				var component = this;
				var li = $('<li class="' + component.component + '"></li>');

				component.textarea = textarea;
				component.onRender(textarea).appendTo(li);
				li.appendTo(ul);
			});

			toolbar.append(ul);

			//$(textarea).bind($.browser.opera ? 'keypress' : 'keydown', function(e)
			$(textarea).bind('keydown', function(e)
			{
				if ((e.which == 9 || e.keyCode == 9) && e.shiftKey)
				{
					textarea.insertAtCaret("\t", '', "");

					e.preventDefault();
					return false;
				}
			});

		});
	};

	$.fn.extend(
	{
		insertAtCaret: function(openWith, closeWith, value)
		{
			var element = this[0];

			if (document.selection)
			{
				element.focus();
				sel = document.selection.createRange();
				sel.text = openWith + (sel.text.length > 0 ? sel.text : value) + closeWith;

				element.focus();
			}
			else if (element.selectionStart || element.selectionStart == '0')
			{
				var startPos = element.selectionStart;
				var endPos = element.selectionEnd;
				var scrollTop = element.scrollTop;

				if (startPos != endPos)
				{
					var value = openWith + element.value.substring(startPos, endPos) + closeWith;
				}
				else
				{
					var value = openWith + value + closeWith;
				}

				element.value = element.value.substring(0, startPos) + value + element.value.substring(endPos, element.value.length);

				element.focus();
				element.selectionStart = startPos + value.length;
				element.selectionEnd = startPos + value.length;
				element.scrollTop = scrollTop;
			}
			else
			{
				element.value += (openWith + value + closeWith);
				element.focus();
			}
		}
	});
}
)(jQuery);
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
//# sourceMappingURL=jquery.wikieditor.js.map