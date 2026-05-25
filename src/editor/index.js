import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { ToggleControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const FeaturedPostPanel = () => {
	const { postType, isFeatured, metaKey } = useSelect( ( select ) => {
		const editor = select( 'core/editor' );
		const type = editor.getCurrentPostType();
		const key = type + '_featured';
		const meta = editor.getEditedPostAttribute( 'meta' ) || {};
		return {
			postType: type,
			metaKey: key,
			isFeatured: meta[ key ] === '1',
		};
	}, [] );

	const { editPost } = useDispatch( 'core/editor' );

	const enabledTypes = window.wpfpEditor?.postTypes || [];
	if ( ! enabledTypes.includes( postType ) ) {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="wpfp-featured-post"
			title={ __( 'Featured Post', 'wp-featured-posts' ) }
		>
			<ToggleControl
				label={ __( 'Mark as Featured', 'wp-featured-posts' ) }
				checked={ isFeatured }
				onChange={ ( value ) => {
					editPost( {
						meta: { [ metaKey ]: value ? '1' : '0' },
					} );
				} }
			/>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wpfp-featured-post', {
	render: FeaturedPostPanel,
	icon: 'star-filled',
} );
