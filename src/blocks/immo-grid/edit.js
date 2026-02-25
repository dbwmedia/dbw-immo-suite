import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

export default function Edit({ attributes, setAttributes }) {
    const { postsPerPage, marketing, propertyType, hidePrice, showDate } = attributes;
    const blockProps = useBlockProps();

    // Fetch tax terms for the editor dynamically
    const marketingTerms = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'vermarktungsart', { per_page: -1 });
    }, []);

    const propertyTypeTerms = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'objektart', { per_page: -1 });
    }, []);

    const marketingOptions = [
        { label: __('Alle', 'dbw-immo-suite'), value: '' }
    ];
    if (marketingTerms) {
        marketingTerms.forEach((term) => {
            marketingOptions.push({ label: term.name, value: term.slug });
        });
    }

    const typeOptions = [
        { label: __('Alle', 'dbw-immo-suite'), value: '' }
    ];
    if (propertyTypeTerms) {
        propertyTypeTerms.forEach((term) => {
            typeOptions.push({ label: term.name, value: term.slug });
        });
    }

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Allgemeine Einstellungen', 'dbw-immo-suite')}>
                    <RangeControl
                        label={__('Anzahl Immobilien', 'dbw-immo-suite')}
                        value={postsPerPage}
                        onChange={(value) => setAttributes({ postsPerPage: value })}
                        min={1}
                        max={24}
                    />
                    <ToggleControl
                        label={__('Kaufpreis/Miete ausblenden', 'dbw-immo-suite')}
                        checked={hidePrice}
                        onChange={(value) => setAttributes({ hidePrice: value })}
                    />
                    <ToggleControl
                        label={__('Einstelldatum anzeigen', 'dbw-immo-suite')}
                        checked={showDate}
                        onChange={(value) => setAttributes({ showDate: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Filter', 'dbw-immo-suite')} initialOpen={true}>
                    <p className="components-base-control__help">{__('Nur Immobilien anzeigen, die folgende Kriterien erfüllen:', 'dbw-immo-suite')}</p>
                    <SelectControl
                        label={__('Vermarktungsart', 'dbw-immo-suite')}
                        value={marketing}
                        options={marketingOptions}
                        onChange={(value) => setAttributes({ marketing: value })}
                    />
                    <SelectControl
                        label={__('Objektart', 'dbw-immo-suite')}
                        value={propertyType}
                        options={typeOptions}
                        onChange={(value) => setAttributes({ propertyType: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <div className="models-preview-placeholder">
                <div className="components-placeholder__label">{__('DBW Immo Grid', 'dbw-immo-suite')}</div>
                <div className="components-placeholder__fieldset">
                    <p>{__('Vorschau in Entwicklung. Zeigt Immobilien an.', 'dbw-immo-suite')}</p>
                    <ul>
                        <li>Anzahl: {postsPerPage}</li>
                        <li>Vermarktung: {marketing || 'Alle'}</li>
                        <li>Typ: {propertyType || 'Alle'}</li>
                    </ul>
                </div>
            </div>
        </div>
    );
}
