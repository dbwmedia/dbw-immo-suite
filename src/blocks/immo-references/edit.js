import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, CheckboxControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
    const { status, hidePrice, showDate, postsPerPage, location, columns } = attributes;

    const blockProps = useBlockProps();

    // Fetch location terms dynamically
    const locationTerms = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'ort', { per_page: -1 });
    }, []);

    const locationOptions = [
        { label: __('Alle Orte', 'dbw-immo-suite'), value: '' }
    ];
    if (locationTerms) {
        locationTerms.forEach((term) => {
            locationOptions.push({ label: term.name + ' (' + term.count + ')', value: term.slug });
        });
    }

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
                <PanelBody title={__('Darstellung', 'dbw-immo-suite')}>
                    <RangeControl
                        label={__('Anzahl Immobilien', 'dbw-immo-suite')}
                        value={postsPerPage}
                        onChange={(value) => setAttributes({ postsPerPage: value })}
                        min={1}
                        max={24}
                    />
                    <RangeControl
                        label={__('Spalten', 'dbw-immo-suite')}
                        value={columns}
                        onChange={(value) => setAttributes({ columns: value })}
                        min={1}
                        max={4}
                    />
                    <ToggleControl
                        label={__('Preis ausblenden', 'dbw-immo-suite')}
                        checked={hidePrice}
                        onChange={(value) => setAttributes({ hidePrice: value })}
                    />
                    <ToggleControl
                        label={__('Verkaufsdatum anzeigen', 'dbw-immo-suite')}
                        checked={showDate}
                        onChange={(value) => setAttributes({ showDate: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Filter', 'dbw-immo-suite')} initialOpen={true}>
                    <SelectControl
                        label={__('Ort / Stadt', 'dbw-immo-suite')}
                        help={__('Ideal fuer Geo-Landing-Pages: Zeige nur Referenzen aus einer bestimmten Stadt.', 'dbw-immo-suite')}
                        value={location}
                        options={locationOptions}
                        onChange={(value) => setAttributes({ location: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Status Filter', 'dbw-immo-suite')} initialOpen={false}>
                    <p className="components-base-control__help">{__('Welche Status sollen angezeigt werden?', 'dbw-immo-suite')}</p>
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
