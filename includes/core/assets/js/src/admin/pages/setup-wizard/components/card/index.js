/**
 * Internal dependencies
 */
import { Container, Header, Footer, Body } from './styles.js';

export function Card( props ) {
	return <Container { ...props } />;
}

export function CardHeader( { title, supTitle, children, ...props } ) {
	return (
		<Header { ...props }>
			<div>
				{ supTitle && <small>{ supTitle }</small> }
				{ title && <h1>{ title }</h1> }
			</div>
			{ children }
		</Header>
	);
}

export function CardFooter( props ) {
	return <Footer { ...props } />;
}

export function CardBody( props ) {
	return <Body { ...props } />;
}
