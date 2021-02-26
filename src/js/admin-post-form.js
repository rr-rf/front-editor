export default ($, Swal) => {
    // Make plugin menu active if we in editing page
    $('li.wp-has-current-submenu').removeClass('wp-has-current-submenu')
    $('li.toplevel_page_front_editor_settings').addClass('wp-has-current-submenu')


    let localizeData = window.fe_post_form_data,
        admin_form_builder_nonce = $('#admin_form_builder_nonce').val(),
        formBuilderContainer = false,
        current_forBuilder_controls,
        updated_forBuilder_controls,
        formBuilderOptions;

    let options = {
        notify: {
            error: function (message) {
                return Swal.fire(message, '', 'error');
            },
            success: function (message) {
                return Swal.fire(message, '', 'success');

            },
            warning: function (message) {
                return Swal.fire(message);
            }
        },
        onAddField: function (fieldId) {
            const currentFieldId = fieldId,
                field = $(`#${fieldId}`),
                field_type = field.attr('type'),
                field_count_in_form = $(`.form-field[type="${field_type}"]`).length;

            /**
             * Check for max of type exactable for form
             */
            if ((field_type in formBuilderOptions.temp_back)
                && ('max_in_form' in formBuilderOptions.temp_back[field_type])
                && (formBuilderOptions.temp_back[field_type].max_in_form < field_count_in_form)) {
                formBuilderContainer.actions.removeField(currentFieldId);
                Swal.fire(`Oops...`, formBuilderOptions.messages.max_fields_warning);
            }

            //TODO Add logic for pro version fields

        }
    };



    /**
     * Activate form builder
     */
    $(window).on('load', () => {
        updateFormBuilder();
    })

    /**
     * Update form builder
     */
    function updateFormBuilder() {
        let post_type = $('#fe_settings_post_type').val();
        wp.ajax.send('fe_get_formBuilder_data', {
            data: {
                post_id: localizeData.post_id,
                post_type: post_type,
                admin_form_builder_nonce: admin_form_builder_nonce
            },
            success: function (response) {
                let formBuilderId = `form-builder`;

                $(`#${formBuilderId}`).remove();
                $('.formBuilder-wrapper').empty().append(`<div id="${formBuilderId}"></div>`);

                formBuilderOptions = response.formBuilder_options;

                let templates_obj = formBuilderOptions.temp_back;

                /**
                 * Creating templates using data form backend
                 */
                Object.keys(templates_obj).map((key, index) => {
                    formBuilderOptions.templates[key] = (fieldData) => {
                        return {
                            field: templates_obj[key].field,
                            onRender: function () {
                                $(document.getElementById(fieldData.name));
                            }
                        }
                    }
                });
                /**
                * Init formBuilder
                */
                formBuilderContainer = $(`#${formBuilderId}`).formBuilder(
                    { ...options, ...formBuilderOptions }
                );
                formBuilderContainer.promise.then(formBuilder => {
                    console.log(formBuilderOptions)
                    // Remove controls on ajax request if there do not needed
                    builder_control_controls(formBuilderOptions);

                    // Add groups
                    add_groups()

                    // Disable pro fields
                    formBuilderOptions.disable_attr.map((val) => {
                        $(document).find(val).prop('disabled', true)
                    });

                    // Adding data 
                    formBuilderContainer.actions.setData(response.formBuilderData);

                });
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

    /**
     * Saving form data
     */
    function save_form_data() {

        var formArray = $('#fe-fromBuilder').serializeArray(),
            data = objectifyForm(formArray);

        data.formBuilderData = formBuilderContainer.actions.getData('json', true);
        data.action = 'save_post_front_settings';

        console.log(data)
        wp.ajax.send('save_post_front_settings', {
            data: data,
            success: function (response) {
                let message = response.message;
                $('#post_id').val(response.post_id)
                Swal.fire(message.title, message.message, message.status);
            },
            error: function (error) {
                console.log(error);
            }
        });

    }

    function objectifyForm(formArray) {
        //serialize data function
        var returnArray = {};
        for (var i = 0; i < formArray.length; i++) {
            returnArray[formArray[i]['name']] = formArray[i]['value'];
        }
        return returnArray;
    }

    /**
     * Update for builder on post type update
     */
    $('#fe_settings_post_type').on('change', function (ev) {
        ev.preventDefault();
        updateFormBuilder();
    });

    /**
     * Save data
     */
    $('#save-form-post').on('click', function (ev) {
        ev.preventDefault();
        save_form_data();
    });

    /**
     * Remove controls on ajax request if there do not needed
     * @param {*} formBuilderOptions 
     */
    function builder_control_controls(formBuilderOptions) {
        current_forBuilder_controls = [];
        updated_forBuilder_controls = [];

        // Getting current controls
        $('.frmb-control li').each((index, elem) => {
            let data_type = $(elem).attr('data-type');
            current_forBuilder_controls.push(data_type)
        })

        /**
         * Creat array with updated controls
         */
        Object.keys(formBuilderOptions.temp_back).map((key, index) => {
            updated_forBuilder_controls.push(key)
        });

        /**
         *  default fields and custom fields
         */
        updated_forBuilder_controls = [...formBuilderOptions.defaultControls, ...updated_forBuilder_controls]

        /**
         * Find difference between controls
         */
        let difference = current_forBuilder_controls.filter(x => !updated_forBuilder_controls.includes(x));

        difference.map((val) => {
            $(`[data-type="${val}"]`).remove();
        })
    }

    /**
     * Adding group fields
     */
    function add_groups() {
        let controls_group = formBuilderOptions.controls_group
        Object.keys(controls_group).map((key, index) => {
            $(`<p class="group-name ${key}">${controls_group[key].label}</p>`).insertBefore(`[data-type="${controls_group[key].types[0]}"]`);
        })
    }
}