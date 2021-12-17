(function($) {

class Balloon {
    constructor($parent) {
        this.$parent = $parent;
		this.$balloon = undefined;

		const this_ = this;
        
        $parent.on('click', function() {
            this_.pop("これは飾りです");
        });
        $parent.mouseleave(function(e) {
            this_.remove();
        });
    }

    pop(message) {
        this.$balloon = $(`<div class="balloon">${message}</div>`);
        const offset = this.$parent.offset();

        this.$balloon.offset({top: offset.top + 50, left: offset.left - 20});
        $('body').append(this.$balloon);
    }

    remove() {
		if(this.$balloon !== undefined) {
			this.$balloon.remove();
			this.$balloon = undefined;
		}
    }
}

$('.top3-box, .menu, .login, .navigation-button1, .navigation-button2, .navigation-button3, .navigation-button4').each(function() {
    new Balloon($(this));
});

})(jQuery);