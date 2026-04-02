import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
    const { postsPerPage, marketing, propertyType, hidePrice, showDate, onlyHighlights } = attributes;
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
                    <ToggleControl
                        label={__('🌟 Nur Highlights anzeigen', 'dbw-immo-suite')}
                        help={__('Zeigt nur Immobilien an, bei denen "Als Highlight markieren" gesetzt ist.', 'dbw-immo-suite')}
                        checked={onlyHighlights}
                        onChange={(value) => setAttributes({ onlyHighlights: value })}
                    />
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

            <div id="dbw-immo-suite">
                <ServerSideRender
                    block="dbw/immo-grid"
                    attributes={attributes}
                />
            </div>
        </div>
    );
}
