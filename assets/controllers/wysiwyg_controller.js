import { Controller } from '@hotwired/stimulus';
import {
	ClassicEditor,
	Alignment,
	AutoLink,
	Autosave,
	BalloonToolbar,
	BlockQuote,
	Bold,
	Code,
	CodeBlock,
	Essentials,
	FontBackgroundColor,
	FontColor,
	FontFamily,
	FontSize,
	GeneralHtmlSupport,
	Heading,
	Highlight,
	HorizontalLine,
	HtmlEmbed,
	Indent,
	IndentBlock,
	Italic,
	Link,
	List,
	Paragraph,
	PasteFromOffice,
	RemoveFormat,
	SourceEditing,
	SpecialCharacters,
	Strikethrough,
	Subscript,
	Superscript,
	Table,
	TableCaption,
	TableCellProperties,
	TableColumnResize,
	TableProperties,
	TableToolbar,
	TodoList,
	Underline
} from './../js/ckeditor/ckeditor5.js';

import translation from './../js/ckeditor/translations/fr.js';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="wysiwyg" attribute will cause
 * this controller to be executed. The name "wysiwyg" comes from the filename:
 * wysiwyg_controller.js -> "wysiwyg"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
            
        /**
         * Create a free account with a trial: https://portal.ckeditor.com/checkout?plan=free
         */
        const LICENSE_KEY = 'GPL'; // or <YOUR_LICENSE_KEY>.

        const editorConfig = {
            toolbar: {
                items: [
                    'undo',
                    'redo',
                    '|',
                    'sourceEditing',
                    '|',
                    'heading',
                    '|',
                    'fontSize',
                    'fontFamily',
                    'fontColor',
                    'fontBackgroundColor',
                    '|',
                    'bold',
                    'italic',
                    'underline',
                    'strikethrough',
                    'subscript',
                    'superscript',
                    'code',
                    'removeFormat',
                    '|',
                    'specialCharacters',
                    'horizontalLine',
                    'link',
                    'insertTable',
                    'highlight',
                    'blockQuote',
                    'codeBlock',
                    'htmlEmbed',
                    '|',
                    'alignment',
                    '|',
                    'bulletedList',
                    'numberedList',
                    'todoList',
                    'outdent',
                    'indent'
                ],
                shouldNotGroupWhenFull: true
            },
            plugins: [
                Alignment,
                AutoLink,
                Autosave,
                BalloonToolbar,
                BlockQuote,
                Bold,
                Code,
                CodeBlock,
                Essentials,
                FontBackgroundColor,
                FontColor,
                FontFamily,
                FontSize,
                GeneralHtmlSupport,
                Heading,
                Highlight,
                HorizontalLine,
                HtmlEmbed,
                Indent,
                IndentBlock,
                Italic,
                Link,
                List,
                Paragraph,
                PasteFromOffice,
                RemoveFormat,
                SourceEditing,
                SpecialCharacters,
                Strikethrough,
                Subscript,
                Superscript,
                Table,
                TableCaption,
                TableCellProperties,
                TableColumnResize,
                TableProperties,
                TableToolbar,
                TodoList,
                Underline
            ],
            balloonToolbar: ['bold', 'italic', '|', 'link', '|', 'bulletedList', 'numberedList'],
            fontFamily: {
                supportAllValues: true
            },
            fontSize: {
                options: [10, 12, 14, 'default', 18, 20, 22],
                supportAllValues: true
            },
            heading: {
                options: [
                    {
                        model: 'paragraph',
                        title: 'Paragraph',
                        class: 'ck-heading_paragraph'
                    },
                    {
                        model: 'heading2',
                        view: 'h2',
                        title: 'Heading 2',
                        class: 'ck-heading_heading2'
                    },
                    {
                        model: 'heading3',
                        view: 'h3',
                        title: 'Heading 3',
                        class: 'ck-heading_heading3'
                    },
                    {
                        model: 'heading4',
                        view: 'h4',
                        title: 'Heading 4',
                        class: 'ck-heading_heading4'
                    },
                    {
                        model: 'heading5',
                        view: 'h5',
                        title: 'Heading 5',
                        class: 'ck-heading_heading5'
                    },
                    {
                        model: 'heading6',
                        view: 'h6',
                        title: 'Heading 6',
                        class: 'ck-heading_heading6'
                    }
                ]
            },
            htmlSupport: {
                allow: [
                    {
                        name: /^.*$/,
                        styles: true,
                        attributes: true,
                        classes: true
                    }
                ]
            },
            licenseKey: LICENSE_KEY,
            link: {
                addTargetToExternalLinks: true,
                defaultProtocol: 'https://',
                decorators: {
                    toggleDownloadable: {
                        mode: 'manual',
                        label: 'Downloadable',
                        attributes: {
                            download: 'file'
                        }
                    }
                }
            },
            placeholder: 'Type or paste your content here!',
            translation: translation,
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
            }
        };

        const recipe_content = document.querySelector('#recipe_content');
        if(recipe_content) {
            ClassicEditor.create(recipe_content, editorConfig)
            .then(editor => {
                editor.model.document.on("change",(e => {
                    const editorData = editor.getData();
                    editor.value = editorData;
                }));
            }).catch(err => {
                console.error(err.stack);
            });
        }

    }
}