import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
    return (
        <div {...useBlockProps.save()}>
            {/* The block is rendered via PHP on the frontend. */}
        </div>
    );
}
