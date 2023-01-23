/* global simpayAdminPageActivityReports */

/**
 * WordPress dependencies
 */
import {
	Button,
	Dropdown,
	Popover,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { cog } from '@wordpress/icons';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { useUserPreference } from '@wpsimplepay/charts';

const {
	user_id: userId,
	currencies,
	default_currency: defaultCurrency,
} = simpayAdminPageActivityReports;

function Config() {
	const [ currency, setCurrency, isSaving ] = useUserPreference(
		userId,
		'simpay_activity_reports_currency',
		defaultCurrency
	);

	function onSubmit() {
		const url = addQueryArgs( window.location.href, {
			currency,
		} );

		window.location.href = url;
	}

	return (
		<>
			<Dropdown
				position="bottom center"
				popoverProps={ {
					noArrow: false,
				} }
				renderToggle={ ( { isOpen, onToggle } ) => (
					<Button
						variant="secondary"
						onClick={ onToggle }
						aria-expanded={ isOpen }
						icon={ cog }
						tooltip={ __( 'Settings', 'simple-pay' ) }
					/>
				) }
				renderContent={ () => (
					<>
						<SelectControl
							label={ __( 'Currency', 'simple-pay' ) }
							value={ currency }
							onChange={ setCurrency }
							options={ currencies.map( ( currencyCode ) => ( {
								label: currencyCode,
								value: currencyCode.toLowerCase(),
							} ) ) }
							help={ __(
								'Activity with the selected currency and global payment mode will be shown.',
								'simple-pay'
							) }
						/>

						<Button
							type="submit"
							variant="primary"
							onClick={ onSubmit }
							isBusy={ isSaving }
							disabled={ isSaving }
						>
							{ __( 'Update', 'simple-pay' ) }
						</Button>
					</>
				) }
			/>
			<Popover.Slot />
		</>
	);
}

export default Config;
