import Config from './config';

class Tags {
    constructor(options) {
        let defaults = {
            input: '#tag',
            dropdown: '.tag-dropdown',
            container: '#tags-container',
            remove: '.btn-remove',
            onSelect: function (value) {}
        };

        this.setup = $.extend(defaults, options);
        this.input = $(this.setup.input);

        if (!this.input.length) {
            return;
        }

        this.dropdown = $(this.setup.dropdown);
        this.container = $(this.setup.container);
        this.selectedIndex = -1;

        this.dropdown.css({
            'width':			this.input.outerWidth() - 4,
            'left':				this.input.position().left,
            'top':				this.input.position().top + this.input.outerHeight()
        });

        this._onFocus();
        this._onKeyUp();
        this._onKeyDown();
        this._onHover();
        this._onItemClick();

        $(document).bind('click', e => {
            let $target = $(e.target);

            if (!$target.is(this.input)) {
                this._hideDropdown();
            }
        });
    }

    _onKeyUp() {
        this.input.on('keyup', e => {
            let keyCode = e.keyCode || window.event.keyCode;

            if (keyCode === 13) {
                if ($('li.hover', this.dropdown).find('span').text() !== '') {
                    this.addTag($('li.hover', this.dropdown).find('span').text());
                }
                else if (this.input.val() !== '') {
                    this.addTag(this.input.val());
                }

                this._hideDropdown();
                this.input.val('');
                this._loadDefaultTags();

                e.preventDefault();
            }
            else if (keyCode === 40) { // down
                this.select(this.selectedIndex + 1);
                this._showDropdown();
            }
            else if (keyCode === 38) { // up
                this.select(this.selectedIndex - 1);
            }
            else if (keyCode === 27) {
                this._hideDropdown();
            }
            else {
                let searchText = this.input.val().toLowerCase();
                let hits = 0;

                let popularTags = Config.get('popular_tags');
                let dropdown = '';

                for (let value in popularTags) {
                    if (popularTags.hasOwnProperty(value) && value.startsWith(searchText)) {
                        dropdown += this._buildTagItem(value, popularTags[value]);
                        hits++;
                    }
                }

                if (!hits) {
                    dropdown = `<li><small>Dodaj...</small> <span>${searchText}</span></li>`;
                    hits = 1;
                }

                this.dropdown.html(dropdown).toggle(hits >= 1);
            }
        });
    }

    _buildTagItem(value, count) {
        return `<li><span>${value}</span> <small>× ${count}</small></li>`;
    }

    _loadDefaultTags() {
        let popularTags = Config.get('popular_tags');
        let dropdown = '';

        for (let value in popularTags) {
            if (popularTags.hasOwnProperty(value)) {
                dropdown += this._buildTagItem(value, popularTags[value]);
            }
        }

        this.dropdown.html(dropdown);
    }

    _onKeyDown() {
        this.input.on('keydown', e => {
            let keyCode = e.keyCode || window.event.keyCode;

            if (keyCode === 27) {
                this.input.val('');
                this.dropdown.hide();
            }
            else if (keyCode === 13) {
                e.preventDefault();
            }
        });
    }

    _onHover() {
        this.dropdown
            .on('mouseenter', 'li', e => {
                $(e.currentTarget).addClass('hover');
            })
            .on('mouseleave', 'li', e => {
                $(e.currentTarget).removeClass('hover');
            });
    }

    _onItemClick() {
        this.dropdown.on('click', 'li', e => {
            this.addTag($(e.currentTarget).find('span').text());
            this._hideDropdown();

            this.input.val('').focus();
            this._loadDefaultTags();
        });
    }

    _onSuggestionClick() {
        $(this.setup.suggestion).click(e => {
            this.addTag($(e.currentTarget).text());
        });
    }

    _onFocus() {
        this.input.on('focus click', () => {
            this.dropdown.show();
        });
    }

    select(position) {
        let length = $('li:visible', this.dropdown).length;

        if (length > 0) {
            if (position >= length) {
                position = 0;
            }
            else if (position < 0) {
                position = length -1;
            }
            this.selectedIndex = position;

            $('li:visible', this.dropdown).removeClass('hover');
            $('li:visible:eq(' + this.selectedIndex + ')', this.dropdown).addClass('hover');

            this.dropdown.scrollTop(position * $('li:first', this.dropdown).outerHeight());
        }
    }

    _hideDropdown() {
        this.dropdown.hide();
    }

    _showDropdown() {
        this.dropdown.show();
    }

    addTag(value) {
        value = $.trim(value)
            .toString()
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/'/g, "&#39;")
            .replace(/"/g, "&#34;")
            .toLowerCase()
            .replace(/\//g, '')
            .replace(/ /g, '-')
            .replace(/[^a-ząęśżźćółń0-9\-\.#\+\s]/gi, '');

        if (value.startsWith('#')) {
            value = value.substr(1);
        }

        this.setup.onSelect(value);

        this.selectedIndex = - 1;
        $('li', this.dropdown).removeClass('hover').show();
    }
}

export default Tags;
