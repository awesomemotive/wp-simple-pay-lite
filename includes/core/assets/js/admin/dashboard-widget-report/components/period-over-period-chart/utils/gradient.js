export const createLinearGradient = ( canvas, chartArea, rgb ) => {
	const gradient = canvas.createLinearGradient(
		0,
		chartArea.bottom,
		0,
		chartArea.top
	);

	gradient.addColorStop(
		0,
		`rgba(${ rgb[ 0 ] }, ${ rgb[ 1 ] }, ${ rgb[ 2 ] }, 0)`
	);

	gradient.addColorStop(
		1,
		`rgba(${ rgb[ 0 ] }, ${ rgb[ 1 ] }, ${ rgb[ 2 ] }, 0.4)`
	);

	return gradient;
};
