import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import metadata from './block.json';

const icon = (
	<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
		<defs>
			<linearGradient id="immo-grid-icon-grad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
				<stop offset="0" stopColor="#ea2b1f" />
				<stop offset="0.25" stopColor="#ff3c6f" />
				<stop offset="0.5" stopColor="#ff4fdd" />
				<stop offset="0.75" stopColor="#7e56ff" />
				<stop offset="1" stopColor="#00b2ff" />
			</linearGradient>
			<mask id="immo-grid-icon-mask">
				<rect x="3" y="3" width="18" height="18" rx="2.5" fill="#fff" />
				<rect x="5" y="5" width="6" height="7" rx="1" fill="#000" />
				<rect x="13" y="5" width="6" height="7" rx="1" fill="#000" />
				<rect x="5" y="14.5" width="6" height="1.5" rx="0.7" fill="#000" />
				<rect x="13" y="14.5" width="6" height="1.5" rx="0.7" fill="#000" />
				<rect x="5" y="17.5" width="4" height="1.2" rx="0.6" fill="#000" />
				<rect x="13" y="17.5" width="4" height="1.2" rx="0.6" fill="#000" />
			</mask>
		</defs>
		<rect x="3" y="3" width="18" height="18" rx="2.5" fill="url(#immo-grid-icon-grad)" mask="url(#immo-grid-icon-mask)" />
	</svg>
);

registerBlockType(metadata.name, {
	icon,
	edit: Edit,
	save,
});
