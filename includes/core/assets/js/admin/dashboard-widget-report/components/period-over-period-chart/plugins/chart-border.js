/**
 * Draws a border around the entire chart (excluding labels).
 */
export default {
	id: 'chartBorder',
	beforeDraw( chart, args, options ) {
		const {
			ctx,
			chartArea: { left, top, width, height },
		} = chart;

		ctx.save();
		ctx.strokeStyle = options.borderColor || '#ccc';
		ctx.lineWidth = options.borderWidth || 1;
		ctx.strokeRect( left, top, width, height );
		ctx.restore();
	},
};
