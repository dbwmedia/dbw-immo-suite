/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * Validates the allowed types of children.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/
 */
import { PanelBody, ToggleControl, RangeControl, CheckboxControl } from '@wordpress/components';

/**
 * The edit function describes the structure of your block in the context of the editor.
 * This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
    const { status, hidePrice, showDate, postsPerPage } = attributes;

    const blockProps = useBlockProps();

    const updateStatus = (value, isChecked) => {
        let newStatus = [...status];
        if (isChecked) {
            newStatus.push(value);
        } else {
            newStatus = newStatus.filter((s) => s !== value);
        }
        setAttributes({ status: newStatus });
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Einstellungen', 'dbw-immo-suite')}>
                    <RangeControl
                        label={__('Anzahl Immobilien', 'dbw-immo-suite')}
                        value={postsPerPage}
                        onChange={(value) => setAttributes({ postsPerPage: value })}
                        min={3}
                        max={24}
                    />
                    <ToggleControl
                        label={__('Preis ausblenden (bei Verkauft)', 'dbw-immo-suite')}
                        checked={hidePrice}
                        onChange={(value) => setAttributes({ hidePrice: value })}
                    />
                    <ToggleControl
                        label={__('Verkaufsdatum anzeigen', 'dbw-immo-suite')}
                        checked={showDate}
                        onChange={(value) => setAttributes({ showDate: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Status Filter', 'dbw-immo-suite')} initialOpen={false}>
                    <p className="components-base-control__help">{__('Wähle aus, welche Status angezeigt werden sollen.', 'dbw-immo-suite')}</p>
                    <CheckboxControl
                        label="Verkauft"
                        checked={status.includes('verkauft')}
                        onChange={(isChecked) => updateStatus('verkauft', isChecked)}
                    />
                    <CheckboxControl
                        label="Referenz"
                        checked={status.includes('referenz')}
                        onChange={(isChecked) => updateStatus('referenz', isChecked)}
                    />
                    <CheckboxControl
                        label="Reserviert"
                        checked={status.includes('reserviert')}
                        onChange={(isChecked) => updateStatus('reserviert', isChecked)}
                    />
                </PanelBody>
            </InspectorControls>

            <div className="models-preview-placeholder">
                <div className="components-placeholder__label">{__('DBW Immo Referenzen', 'dbw-immo-suite')}</div>
                <div className="components-placeholder__fieldset">
                    <p>{__('Vorschau der Referenz-Objekte', 'dbw-immo-suite')}</p>
                    <ul>
                        <li>Status: {status.join(', ')}</li>
                        <li>Anzahl: {postsPerPage}</li>
                    </ul>
                </div>
            </div>
        </div>
    );
}
