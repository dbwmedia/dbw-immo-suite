import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, CheckboxControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

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

            <div id="dbw-immo-suite">
                <ServerSideRender
                    block="dbw/immo-references"
                    attributes={attributes}
                />
            </div>
        </div>
    );
}
