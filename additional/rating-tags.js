(function($) {

// profileまたはcourseページでない
if(!$('.overlay-rating').length) return;

function isTagSafe(tag) {
	let isSafe = false;

	$("#add-rating-tag-list").children('option').each(function() {
		isSafe |= $(this).val() === tag;
	});

	return isSafe;
}

class RatingTagsHandler {
    constructor($ratingTags) {
        this.$ratingTags = $ratingTags;
		this.$addRatingTag = this.$ratingTags.find('.add-rating-tag');
		this.$addRatingTagInput = this.$addRatingTag.find('.add-rating-tag-input');
		this.courseId = parseInt(this.$ratingTags.data('course_id'));
		if(this.$ratingTags.data('is_omnibus')) {
			this.lecturerId = parseInt(this.$ratingTags.data('lecturer_id'));
		} else {
			this.lecturerId = 0;
		}

		const this_ = this;

        this.$ratingTags.on('click', '.rating-tag', function() {
			// 表示/非表示
			let count = parseInt($(this).data('count'));
			$(this).toggleClass('user-selected');
			if($(this).hasClass('user-selected')) {
				count++;
				$(this).find('.pushed-count').text(count);
				$(this).data('count', count);
			} else {
				count--;
				if(count > 0 || $(this).data('is_default')) {
					$(this).find('.pushed-count').text(count);
					$(this).data('count', count);
					if(count > 0) {
						$(this).addClass('active');
					} else {
						$(this).removeClass('active');
					}
				} else {
					// 非表示かつ0人のものを消す
					$(this).remove();
					// 追加入力が消えていたら見せる
					this_.$addRatingTag.show();
				}
			}

            this_.dispatchTags();
        });

		this.$addRatingTagInput.focus(function() {
			this_.updateAddRatingTag();
        });

		this.$addRatingTag.submit(function() {
			const newTag = this_.$addRatingTagInput.val();

			if(!newTag) {
				return false;
			}

			if(!isTagSafe(newTag)) {
				alert('選択肢にないタグ名です。');
				return false;
			}

			this_.appendTag(newTag);
			this_.dispatchTags();
			this_.updateAddRatingTag();
			this_.$addRatingTagInput.val('');

			return false;
		});

		this.updateAddRatingTag();
    }

	getCurrentTags(onlyUser=false) {
		// ユーザーの既存のタグの取得
		const currentTags = [];
		this.$ratingTags.find('.rating-tag').each(function() {
			if(onlyUser && !$(this).hasClass('user-selected')) return;

			currentTags.push($(this).data('tag'));
		});

		return currentTags;
	}

	updateAddRatingTag() {
		const currentTags = this.getCurrentTags();
		let shouldHide = true;

		$('#add-rating-tag-list option').each(function() {
			if(currentTags.indexOf($(this).val()) === -1) {
				$(this).prop('disabled', false);
				shouldHide = false;
			} else {
				$(this).prop('disabled', true);
			}
		});

		if(shouldHide) this.$addRatingTag.hide();
	}

    dispatchTags() {
		const userRatingTags = this.getCurrentTags(true);

		// ユーザーの全てのタグを送信
		$.ajax({
			type: 'post',
			url: '/wp-content/themes/selva/additional/rating-tags-post.php',
			data: {
				'course_id': this.courseId,
				'lecturer_id': this.lecturerId,
				'rating_tags': userRatingTags
			}
		});
    }

    appendTag(newTag) {
		const userRatingTags = this.getCurrentTags(false);

		// その中にあるタグであればreturn
		if(userRatingTags.indexOf(newTag) !== -1) {
			alert('既に存在しているタグです。');
			return;
		}

		const $newRatingTag = $(`<button class="rating-tag user-selected" data-tag="${newTag}" data-count="1">${newTag}<span class="pushed-count">1</span></button>`);

		this.$ratingTags.find('.add-rating-tag').before($newRatingTag);
    }
}

$('.rating-tags').each(function() {
	new RatingTagsHandler($(this));
});

})(jQuery);