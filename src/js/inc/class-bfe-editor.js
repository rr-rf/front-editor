'use strict';

import EditorJS from '@editorjs/editorjs';

import Header from '@editorjs/header';

import ImageTool from '@editorjs/image';

import Embed from '@editorjs/embed';

import Quote from '@editorjs/quote';

import Marker from '@editorjs/marker';

import CodeTool from '@editorjs/code';

import LinkTool from '@editorjs/link';

import List from '@editorjs/list';

import Delimiter from '@editorjs/delimiter';

import InlineCode from '@editorjs/inline-code';

import RawTool from '@editorjs/raw';

import Warning from '@editorjs/warning';

import Table from '@editorjs/table';

import Checklist from '@editorjs/checklist';

import WPImage from './class-wp-image';

export default class BfeEditor {


    constructor(bfee_editor, data) {
        this.bfee_editor = new EditorJS(this.editorSettings);
        try {
            this.data = JSON.parse(editor_data);
        }
        catch (e) {
            this.data = editor_data;
        }
    }

    get bfee_data() {
        let data;
        try {
            data = JSON.parse(editor_data);
        }
        catch (e) {
            data = editor_data;
        }

        return data;
    }

    static get get_bfee_data() {
        let data;
        try {
            data = JSON.parse(editor_data);
        }
        catch (e) {
            data = editor_data;
        }

        return data;
    }

    /**
     * Save data from admin block
     * @param {*} data 
     */
    save_data(data) {
        let $ = jQuery,
            save_button_messages = this.bfee_data.translations.save_button,
            save_button = document.querySelector('#save-editor-block'),
            editor_block = document.querySelector('#bfe-editor'),
            post_link = document.querySelector('.view-page'),
            thumb_exist = document.querySelector('#bfe-editor .image_loader'),
            bfe_selected_file = document.querySelector('#img_inp').files[0];

        const formData = new FormData();

        var formArray = $('#bfe-editor').serializeArray(),
            formArray_data = this.objectifyForm(formArray);

        formArray_data.action = 'bfe_update_post';

        /**
         * Post image
         */
        if (bfe_selected_file) {
            formArray_data.image = bfe_selected_file;
        }

        /**
         * Sending exist or not post image to understand delete or not it from post
         */
        if (thumb_exist) {
            formArray_data.thumb_exist = thumb_exist.getAttribute('thumb_exist');
        } else {
            formArray_data.thumb_exist = 0;
        }

        /**
         * Updating taxonomy fields
         */
        $('.taxonomy-select').each((index, element) => {
            let element_val = $(element).val(),
                selected_element = element_val.toString(),
                name = $(element).attr('name');
            if (element_val) {
                formArray_data[name] = selected_element;
            } else {
                formArray_data[name] = 'null';
            }
        })

        formArray_data.editor_data = JSON.stringify(data);

        save_button.innerHTML = save_button_messages.updating;
        save_button.disabled = true;

        for (var key in formArray_data) {
            formData.append(key, formArray_data[key]);
        }

         fetch(BfeEditor.get_bfee_data.ajax_url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.success) {
                    save_button.innerHTML = save_button_messages.update;
                    save_button.disabled = false;

                    /**
                     * New post add link and show the button
                     */
                    post_link.setAttribute('href', data.data.url);
                    post_link.classList.remove("hide");

                    editor_block.setAttribute('post_id', data.data.post_id)
                    this.bfee_editor.notifier.show({
                        message: data.data.message,
                        style: 'success',
                    });
                } else {
                    save_button.innerHTML = save_button_messages.update;
                    save_button.disabled = false;

                    this.bfee_editor.notifier.show({
                        message: data.data.message ?? 'Something goes wrong try later',
                        style: 'error',
                    });
                }
            }).catch()
    }

    /**
     * 
     * @param {*} formArray 
     */
    objectifyForm(formArray) {
        //serialize data function
        var returnArray = {};
        for (var i = 0; i < formArray.length; i++) {
            returnArray[formArray[i]['name']] = formArray[i]['value'];
        }
        return returnArray;
    }

    /**
     * Save on change data
     */
    onChangeSaveData() {
        this.bfee_editor.save().then((data) => {
            this.save_data(data);
        });
    }

    /**
     * uploading image
     */
    static uploadImage(file = null, url = null) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();

            let post_id = document.querySelector('#bfe-editor').getAttribute('post_id');

            formData.append('action', 'bfe_uploading_image')

            console.log(post_id)
            formData.append('post_id', post_id)

            if (file !== null) {
                formData.append('image', file)
            }
            if (url !== null) {
                formData.append('image_url', url)
            }

            fetch(BfeEditor.get_bfee_data.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.success) {
                        resolve(data);
                    } else {
                        resolve(data);
                    }
                }).catch()
        })
    }

    static wpMediaUploader() {
        var file_frame;

        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            button: {
                text: jQuery(this).data('uploader_button_text'),
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            var selection = file_frame.state().get('selection').first().toJSON();

            console.log(selection.url)

            // var attachment_ids = selection.map(function (attachment) {
            //     attachment = attachment.toJSON();
            //     var array = {id:attachment.id,url:attachment.url}
            //     return array;
            // }).join();


        });
        file_frame.open();
    }


    /**
     * Setting is set here
     */
    get editorSettings() {
        const editor_settings = this.bfee_data.editor_settings;
        return {
            holder: 'bfe-editor-block',
            placeholder: this.bfee_data.translations.editor_field_placeholder,
            autofocus: true,
            i18n: this.bfee_data.translations.i18n,
            tools: {
                ...(editor_settings.editor_header_plugin && {
                    header: {
                        class: Header,
                        inlineToolbar: true,
                        config: {
                            placeholder: 'Enter a header',
                            levels: [2, 3, 4],
                            defaultLevel: 3
                        },
                        shortcut: 'CMD+SHIFT+H'
                    }
                }),
                ...(editor_settings.editor_gallery_plugin && {
                    wpImageGallery: {
                        class: WPImage,
                        inlineToolbar: true,
                        config: {
                            wp_media_uploader: BfeEditor.wpMediaUploader
                        }
                    }
                }),
                ...(editor_settings.editor_image_plugin && {
                    image: {
                        class: ImageTool,
                        inlineToolbar: true,
                        config: {
                            uploader: {
                                uploadByFile(file) {
                                    return BfeEditor.uploadImage(file).then(data => {
                                        if (!data.success) {
                                            BfeEditor.bfee_editor.notifier.show({
                                                message: data.data.message ?? 'Something goes wrong try later',
                                                style: 'error',
                                            });
                                            return { "success": 0 };
                                        }
                                        return {
                                            "success": 1,
                                            "file": {
                                                "url": data.data.url,
                                            }
                                        };
                                    })
                                },
                                uploadByUrl(url) {
                                    return BfeEditor.uploadImage(null, url).then(data => {
                                        if (!data.success) {
                                            BfeEditor.bfee_editor.notifier.show({
                                                message: data.data.message ?? 'Something goes wrong try later',
                                                style: 'error',
                                            });
                                            return { "success": 0 };
                                        }
                                        return {
                                            "success": 1,
                                            "file": {
                                                "url": data.data.url,
                                            }
                                        };
                                    })
                                }

                            }

                        }
                    }
                }),

                ...(editor_settings.editor_list_plugin && {
                    list: {
                        class: List,
                        inlineToolbar: true,
                        shortcut: 'CMD+SHIFT+L'
                    }
                }),

                ...(editor_settings.editor_checklist_plugin && {
                    checklist: {
                        class: Checklist,
                        inlineToolbar: true,
                    }
                }),

                ...(editor_settings.editor_quote_plugin && {
                    quote: {
                        class: Quote,
                        inlineToolbar: true,
                        config: {
                            quotePlaceholder: 'Enter a quote',
                            captionPlaceholder: 'Quote\'s author',
                        },
                        shortcut: 'CMD+SHIFT+O'
                    },
                }),

                ...(editor_settings.editor_warning_plugin && {
                    warning: Warning,
                }),

                ...(editor_settings.editor_marker_plugin && {
                    marker: {
                        class: Marker,
                        shortcut: 'CMD+SHIFT+M'
                    }
                }),

                ...(editor_settings.editor_code_plugin && {
                    code: {
                        class: CodeTool,
                        shortcut: 'CMD+SHIFT+C'
                    },
                }),

                ...(editor_settings.editor_delimiter_plugin && {
                    delimiter: Delimiter
                }),

                ...(editor_settings.editor_inlineCode_plugin && {
                    inlineCode: {
                        class: InlineCode,
                        shortcut: 'CMD+SHIFT+C'
                    },
                }),

                ...(editor_settings.editor_embed_plugin && {
                    embed: Embed,
                }),

                ...(editor_settings.editor_table_plugin && {
                    table: {
                        class: Table,
                        inlineToolbar: true,
                        shortcut: 'CMD+ALT+T'
                    },
                }),

                //linkTool: LinkTool,
            },

            initialBlock: 'paragraph',

            data: {
                blocks: this.bfee_data.data.blocks
            },
            onReady: () => {
                if (!this.bfee_data.data) {
                    this.bfee_editor.blocks.renderFromHTML(this.bfee_data.html_post_content)
                        .catch(error => {
                            console.log('Error with rendering HTML data ' + error);
                        });
                }
            },
            onChange: () => {
                //this.onChangeSaveData()
            }
        }

    };

}