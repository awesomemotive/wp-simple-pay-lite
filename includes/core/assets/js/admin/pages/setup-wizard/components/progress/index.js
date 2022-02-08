/**
 * WordPress depedencies
 */
import { Icon, check } from '@wordpress/icons';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	Container,
	Line,
	StepIndicator,
	StepIndicatorCurrent,
} from './styles.js';

export function Progress( { current, total } ) {
	const steps = [];

	for ( let i = 1; i <= total; i++ ) {
		let status = 'incomplete';

		if ( i < current || current === total ) {
			status = 'complete';
		} else if ( i === current ) {
			status = 'current';
		}

		steps.push(
			<Fragment key={ i }>
				<StepIndicator
					status={ status }
					isLast={ i === current }
					isFirst={ i === 1 }
					isComplete={ i < current || current === total }
					isCurrent={ i === current }
				>
					<Icon icon={ check } size={ 18 } />
					<StepIndicatorCurrent />
				</StepIndicator>

				{ i !== total && <Line /> }
			</Fragment>
		);
	}

	return <Container>{ steps }</Container>;
}
