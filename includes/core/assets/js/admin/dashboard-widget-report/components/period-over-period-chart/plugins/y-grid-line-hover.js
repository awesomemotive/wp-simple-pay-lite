/**
 * Draws a line on the charts Y axis when hovering a data point.
 */
export default {
	id: 'YGridLineHover',
	afterDraw: ( chart ) => {
		if ( ! chart.tooltip?._active?.length ) {
			return;
		}

		const x = chart.tooltip._active[ 0 ].element.x;
		const yAxis = chart.scales.yAxis;
		const ctx = chart.ctx;

		ctx.save();
		ctx.beginPath();
		ctx.moveTo( x, yAxis.top );
		ctx.lineTo( x, yAxis.bottom );
		ctx.lineWidth = 2;
		ctx.strokeStyle = '#428BCA';
		ctx.stroke();
		ctx.restore();
	},
};
