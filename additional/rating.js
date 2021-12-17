(function($) {

// profileまたはcourseページでない
if(!$('.overlay-rating').length) return;

class StarsRatingHandler {
	constructor($starsRating) {
		this.$starsRating = $starsRating;
	}

	updateStars(stars, count) {
		// 色を塗る星の数
		this.$starsRating.children('.stars:last-child').css({width: stars.toPrecision(2) +'em'});
		// カッコ内の数字
		let summary = '--';
		if(stars > 0) summary = stars.toPrecision(2);
		if(count !== undefined) {
			summary += '、' + String(count) + '件';
		}
		this.$starsRating.find('.stars-summary').text(summary);
	}
}

class OverlayRatingHandler {
	constructor($overlayRating) {
		this.$overlayRating = $overlayRating;
		this.$allMeterBars = $overlayRating.find('.meter-bar');
		this.starsRatingHandler = new StarsRatingHandler($overlayRating.children('.stars-rating'));
		this._fadeTimeoutId = 0;
		this._isAboutToOrHasFaded = false;
		
		const this_ = this;

		this.$overlayRating.hover(function() {
			// マウスがホバーした
			// 消える予約が入っているなら取り消す
			if(this_._isAboutToOrHasFaded) {
				clearTimeout(this_._fadeTimeoutId);
				this_._isAboutToOrHasFaded = false;
			}
			this_.$overlayRating.addClass('active');
		}, function() {
			// マウスが出て行った
			// 0.3秒後に消える予約
			this_._fadeTimeoutId = setTimeout(function() {
				this_.$overlayRating.removeClass('active');
			}, 300);
			this_._isAboutToOrHasFaded = true;
		});
	}
	
	pop(ratingWrapHandler) {
		// 消える予約が入っているなら取り消す
		if(this._isAboutToOrHasFaded) {
			clearTimeout(this._fadeTimeoutId);
			this._isAboutToOrHasFaded = false;
		}

		// 星の更新
		this.starsRatingHandler.updateStars(ratingWrapHandler.overallRatingAverage, ratingWrapHandler.overallRatingCount);
		
		// メーターの更新
		this.$allMeterBars.each(function(index, meterBar) {
			if(ratingWrapHandler.overallRatingCount == 0) {
				$(meterBar).css({width: '0'});
				$(meterBar).parent().next().text('--');
				return;
			}
			const percentage = Math.round(ratingWrapHandler.eachOverallRating(index + 1)
				/ ratingWrapHandler.overallRatingCount * 100) + '%';
			$(meterBar).css({width: percentage});
			$(meterBar).parent().next().text(percentage);
		});

		// 位置の移動
		const offset = ratingWrapHandler.overallRatingHandler.$starsRating.offset();
		this.$overlayRating.offset({top: offset.top - 180, left: offset.left - 72});

		this.$overlayRating.addClass('active');
		this._isAboutToOrHasFaded = true;
	}
	  
	sleepAndFade() {
		// 出ていたら消える。この関数は良く呼び出されるし、sleepするラグがあるので出ていたらという条件付き
		if(this._isAboutToOrHasFaded) {
			const this_ = this;
			this._fadeTimeoutId = setTimeout(function() {
				this_.$overlayRating.removeClass('active');
			}, 300);
			this._isAboutToOrHasFaded = true;
		}
	}
}

class ChartAndSummaryRatingHandler {
	constructor($chartRating, $summaryRating, overlayRatingHandler) {
		this.$chartRating = $chartRating;
		this.$summaryRating = $summaryRating;
		this.$performanceRating = $summaryRating.find('.performance-rating');
		this.overlayRatingHandler = overlayRatingHandler;

		const this_ = this;

		this.starsRatingHandlers = {};
		this.labels = {};

		for(let ratingNo = 1; ratingNo <= 3; ratingNo++) {
			// 授業ページはstars-ratingが存在しない
			const $rating = $summaryRating.find('.stars-rating.rating' + String(ratingNo));
			if($rating.length) {
				this.starsRatingHandlers[ratingNo] = new StarsRatingHandler($rating);

				// マウスをホバーすると$overlayRatingが現れるようにする
				$rating.mouseenter(function(e) {
					// $overallRatingに入ると
					this_.overlayRatingHandler.pop(this_.getRatingWrapHandlerInSummaryRating(ratingNo, $rating));
				});
				$rating.mouseleave(function(e) {
					// $ratingsWrapから出ると
					this_.overlayRatingHandler.sleepAndFade();
				});
			}

			const $ratingLabel = $summaryRating.find('.rating-label.rating' + String(ratingNo));
			if($ratingLabel.length)
				this.labels[ratingNo] = $ratingLabel.text();
		}
		this.ratingWrapHandlers = [];

		if(this.$chartRating.length) {
			this.ctx = $chartRating[0].getContext('2d');
			// キャンバス系
			this.ctx.translate(60, 100); // 描画面の移動
			this.r = 300; // 三角形用
			this.centerX = 230; // 二軸評価用
			this.centerY = 300; // 二軸評価用
			this.x = 500; // 二軸評価用
			this.y = 500; // 二軸評価用
		} else {
			// $chartRatingが存在しないなら、左のスペースを埋める。
			$('.profile_').css('grid-template-columns','1fr');
			$('.profile-right-wrapper').css('padding-left','0');
			$('.item-details').css({'width': 'auto', 'white-space': 'nowrap'});
			$('.profile .summary-rating').css('grid-template-columns', '1fr 1fr');
		}
	}

	pushRatingWrapHandler(ratingWrapHandler) {
		this.ratingWrapHandlers.push(ratingWrapHandler);
	}

	getRatingWrapHandlerInSummaryRating(ratingNo, $ratingInSummaryRating) {
		return new (class {
			constructor(ratingWrapHandlers, ratingNo_, $ratingInSummaryRating_) {
				this.eachOverallRatingCount = {1: 0, 2: 0, 3: 0, 4: 0, 5: 0};
				this.overallRatingCount = 0;
				this.overallRatingAverage = 0;
				this.overallRatingTotal = 0;
				if($ratingInSummaryRating_ !== undefined)
					this.overallRatingHandler = {$starsRating: $ratingInSummaryRating_};

				for(const ratingWrapHandler of ratingWrapHandlers) {
					if(ratingWrapHandler.ratingNo != ratingNo_) continue;

					for(let stars = 1; stars <= 5; stars++) {
						this.eachOverallRatingCount[stars] += ratingWrapHandler.eachOverallRating(stars);
					}
				}

				for(let stars = 1; stars <= 5; stars++) {
					this.overallRatingCount += this.eachOverallRatingCount[stars];
					this.overallRatingTotal += this.eachOverallRatingCount[stars] * stars;
				}

				if(this.overallRatingCount)
					this.overallRatingAverage = this.overallRatingTotal / this.overallRatingCount;
			}

			eachOverallRating(stars) {
				return this.eachOverallRatingCount[stars];
			}
		})(this.ratingWrapHandlers, ratingNo, $ratingInSummaryRating);
	}

	updateRatings() {
		const averages = {};
		const counts = {};
		const fracs = {};
		let countsSum = 0;
		let allAverage = 0;
		for(const ratingNo in this.labels) {
			const ratingWrapHandlersInSummaryRating = this.getRatingWrapHandlerInSummaryRating(ratingNo);
			averages[ratingNo] = ratingWrapHandlersInSummaryRating.overallRatingAverage;
			counts[ratingNo] = ratingWrapHandlersInSummaryRating.overallRatingCount;
			fracs[ratingNo] = averages[ratingNo] / 5;

			allAverage += ratingWrapHandlersInSummaryRating.overallRatingTotal;
			countsSum += counts[ratingNo];
		}
		if(countsSum)
			allAverage /= countsSum;

		let performanceRating = '';
		if(allAverage < 1)        performanceRating = '--';
		else if(allAverage < 1.5) performanceRating = 'D';
		else if(allAverage < 2.5) performanceRating = 'C';
		else if(allAverage < 3.5) performanceRating = 'B';
		else if(allAverage < 4.5) performanceRating = 'A';
		else                      performanceRating = 'S';
	
		// .summary-ratingの変更
		this.$performanceRating.text(performanceRating);
		for(const ratingNo in this.starsRatingHandlers) {
			this.starsRatingHandlers[ratingNo].updateStars(averages[ratingNo], counts[ratingNo]);
		}

		if(Object.keys(counts).length <= 1)
			return;

		// キャンバスの描画
		// 一旦消す
		this.ctx.clearRect(-60, -100, this.$chartRating[0].width, this.$chartRating[0].height);

		if(Object.keys(counts).length === 2) {
			// 2軸での表示

			// 主軸の表示
			this.ctx.strokeStyle = "gray";
			this.ctx.beginPath();
			this.ctx.moveTo(this.centerX - this.x / 2, this.centerY);
			this.ctx.lineTo(this.centerX + this.x / 2, this.centerY);
			this.ctx.lineTo(this.centerX + this.x / 2 - 20, this.centerY + 20);
			this.ctx.moveTo(this.centerX + this.x / 2, this.centerY);
			this.ctx.lineTo(this.centerX + this.x / 2 - 20, this.centerY - 20);
			this.ctx.moveTo(this.centerX, this.centerY + this.y / 2);
			this.ctx.lineTo(this.centerX, this.centerY - this.y / 2);
			this.ctx.lineTo(this.centerX - 20, this.centerY - this.y / 2 + 20);
			this.ctx.moveTo(this.centerX, this.centerY - this.y / 2);
			this.ctx.lineTo(this.centerX + 20, this.centerY - this.y / 2 + 20);
			this.ctx.stroke();

			// 副軸
			this.ctx.strokeStyle = "#ddd";
			for(let f = 4.5; f >= 1.5; f -= 1) {
				const k = (this.x + this.y) * (f - 3) / 5;
				let startY = this.y * 0.45;
				let startX = k - startY;
				let endX = this.x * 0.45;
				let endY = k - endX;
				if(k < 0) {
					startX = -this.x * 0.45;
					startY = k - startX;
					endY = -this.y * 0.45;
					endX = k - endY;
				}

				this.ctx.beginPath();
				this.ctx.moveTo(this.centerX + startX, this.centerY - startY);
				this.ctx.lineTo(this.centerX + endX, this.centerY - endY);
				this.ctx.stroke();
			}

			// 数字の表示
			this.ctx.font = "36px serif";
			this.ctx.fillStyle = "gray";
			this.ctx.textAlign = "center";
			this.ctx.textBaseline = "middle";
			this.ctx.fillText("1", this.centerX - this.x / 2 - 30, this.centerY);
			this.ctx.fillText("5", this.centerX + this.x / 2 + 30, this.centerY);
			this.ctx.fillText("5", this.centerX, this.centerY - this.y / 2 - 30);
			this.ctx.fillText("1", this.centerX, this.centerY + this.y / 2 + 30);
		
			// 評価に使う2軸の選定
			const xy = [];
			for(const ratingNo in this.labels) {
				xy.push(ratingNo);
			}

			// ラベルの表示
			this.ctx.font = "36px meirio";
			this.ctx.fillStyle = "gray";
			this.ctx.textAlign = "center";
			this.ctx.fillText(this.labels[xy[0]], this.centerX, this.centerY - this.y / 2 - 80);
			if(this.labels[xy[1]].length <= 3) {
				// 3文字以下なら1行
				this.ctx.textAlign = "start";
				this.ctx.fillText(this.labels[xy[1]], this.centerX + this.x / 2 + 60, this.centerY);
			} else {
				// 4文字以上で2行
				this.ctx.fillText(this.labels[xy[1]].substr(0, 3), this.centerX + this.x / 2 + 114, this.centerY - 24);
				this.ctx.fillText(this.labels[xy[1]].substr(3, 3), this.centerX + this.x / 2 + 114, this.centerY + 24);
			}

			// 評価の表示
			if(fracs[xy[0]] !== 0 || fracs[xy[1]] !== 0) {
				this.ctx.strokeStyle = "#FB5235";
				this.ctx.fillStyle = "#FB5235"; // rgba(251, 82, 53, 0.2)
				this.ctx.beginPath();

				if(fracs[xy[0]] === 0) {
					this.ctx.arc(this.centerX + this.x * (fracs[xy[1]] - 0.6), this.centerY + this.y * -0.4, 10, 0, Math.PI, true);
					this.ctx.arc(this.centerX + this.x * (fracs[xy[1]] - 0.6), this.centerY + this.y * 0.4, 10, Math.PI, Math.PI * 2, true);
					this.ctx.lineTo(this.centerX + this.x * (fracs[xy[1]] - 0.6) + 10, this.centerY + this.y * -0.4);
				} else if(fracs[xy[1]] === 0) {
					this.ctx.arc(this.centerX + this.x * -0.4, this.centerY + this.y * (0.6 - fracs[xy[0]]), 10, Math.PI / 2, Math.PI * 3 / 2, false);
					this.ctx.arc(this.centerX + this.x * 0.4, this.centerY + this.y * (0.6 - fracs[xy[0]]), 10, -Math.PI / 2, Math.PI / 2, false);
					this.ctx.lineTo(this.centerX + this.x * -0.4, this.centerY + this.y * (0.6 - fracs[xy[0]]) + 10);
				} else {
					this.ctx.arc(this.centerX + this.x * (fracs[xy[1]] - 0.6), this.centerY + this.y * (0.6 - fracs[xy[0]]), 10, 0, Math.PI * 2, true);
				}

				this.ctx.fill();
				this.ctx.stroke();
			}

			// 評定の表示
			this.ctx.font = "700 72px 'Yu gothic'";
			this.ctx.fillStyle = "#FB5235";
			this.ctx.textAlign = "center";
			this.ctx.fillText(performanceRating, this.centerX, this.centerY);

			return;
		}

		// 外枠の三角形の表示
		this.ctx.strokeStyle = "gray";
		for(let f = 1; f >= 0.2; f -= 0.2) {
			this.ctx.beginPath();
			this.ctx.moveTo(this.r, this.r * (1 - f));
			this.ctx.lineTo(this.r * (1 + 1.7320508 / 2 * f), this.r * (1 + 1 / 2 * f));
			this.ctx.lineTo(this.r * (1 - 1.7320508 / 2 * f), this.r * (1 + 1 / 2 * f));
			this.ctx.lineTo(this.r, this.r * (1 - f));
			this.ctx.stroke();
			this.ctx.strokeStyle = "#ddd";
		}
	
		// 内線の表示
		this.ctx.strokeStyle = "gray";
		this.ctx.beginPath();
		this.ctx.moveTo(this.r, 0);
		this.ctx.lineTo(this.r, this.r);
		this.ctx.lineTo(this.r * (1 + 1.7320508 / 2), this.r * (1 + 1 / 2));
		this.ctx.moveTo(this.r, this.r);
		this.ctx.lineTo(this.r * (1 - 1.7320508 / 2), this.r * (1 + 1 / 2));
		this.ctx.stroke();
		
		// 数字の表示
		this.ctx.font = "36px serif";
		this.ctx.fillStyle = "gray";
		this.ctx.textAlign = "center";
		this.ctx.textBaseline = "middle";
		this.ctx.fillText("5", this.r, -30);
		this.ctx.fillText("5", this.r * (1 + 1.7320508 / 2) + 30, this.r * (1 + 1 / 2) + 5);
		this.ctx.fillText("5", this.r * (1 - 1.7320508 / 2) - 36, this.r * (1 + 1 / 2) + 5);
	
		// ラベルの表示
		this.ctx.font = "36px meirio";
		this.ctx.fillStyle = "gray";
		this.ctx.textAlign = "center";
		this.ctx.textBaseline = "middle";
		this.ctx.fillText(this.labels[1], this.r, -78);
		this.ctx.textAlign = "end";
		this.ctx.fillText(this.labels[2], this.r * (1 + 1.7320508 / 2) + 60, this.r * (1 + 1 / 2) + 55);
		this.ctx.textAlign = "start";
		this.ctx.fillText(this.labels[3], this.r * (1 - 1.7320508 / 2) - 80, this.r * (1 + 1 / 2) + 55);

		// 評価の表示
		this.ctx.strokeStyle = "#FB5235";
		this.ctx.fillStyle = "rgba(251, 82, 53, 0.2)"; // rgba(251, 82, 53, 0.2)
		this.ctx.beginPath();
		this.ctx.moveTo(this.r, this.r * (1 - fracs[1]));
		this.ctx.lineTo(this.r * (1 + fracs[2] * 1.7320508 / 2), this.r * (1 + fracs[2] / 2));
		this.ctx.lineTo(this.r * (1 - fracs[3] * 1.7320508 / 2), this.r * (1 + fracs[3] / 2));
		this.ctx.lineTo(this.r, this.r * (1 - fracs[1]));
		this.ctx.stroke();
		this.ctx.fill();
	
		// 評定の表示
		this.ctx.font = "700 72px 'Yu gothic'";
		this.ctx.fillStyle = "#FB5235";
		this.ctx.textAlign = "center";
		this.ctx.textBaseline = "middle";
		this.ctx.fillText(performanceRating, this.r, this.r, 46);
	}
}

class RatingWrapHandler {
  	constructor($ratingWrap, overlayRatingHandler, chartAndSummaryRatingHandler) {
		const $overallRating = $ratingWrap.children('.overall-rating');
		const $userRating = $ratingWrap.children('.user-rating');
		// $overallRatingと$userRatingが存在していなければreturn
		if(!($overallRating.length && $userRating.length)) return;

		this.$ratingWrap = $ratingWrap;
		this.overallRatingHandler = new StarsRatingHandler($overallRating);
		this.userRatingHandler = new StarsRatingHandler($userRating);
		this.overlayRatingHandler = overlayRatingHandler;

		this.courseId = parseInt(this.$ratingWrap.data('course_id'));
		this.lecturerId = parseInt(this.$ratingWrap.data('lecturer_id'));
		this.isOmnibus = this.$ratingWrap.data('is_omnibus') ? true : false;
		this.ratingNo = parseInt(this.$ratingWrap.data('rating_no'));
		this.userStars = parseFloat(this.$ratingWrap.data('user_rating'));

		if(!this.isOmnibus) {
			this.chartAndSummaryRatingHandler = chartAndSummaryRatingHandler;
			this.chartAndSummaryRatingHandler.pushRatingWrapHandler(this);
		}

		// 自分以外の、インデックスの星の数を選んだ人数と
		// 自分以外の、星の数の合計と
		// 自分以外の、星を付けた人数
		this.eachOthersRatings = {};
		this.othersStarsTotal = 0;
		this.othersRatingCount = 0;
		for(let i = 1; i <= 5; i++) {
			this.eachOthersRatings[i] = this.$ratingWrap.data('others_ratings' + i);
			this.othersStarsTotal += parseFloat(this.eachOthersRatings[i]) * i;
			this.othersRatingCount += parseFloat(this.eachOthersRatings[i]);
		}

		const this_ = this;

		// なお以下の3つは変数でありフィールドではない

		// $userRatingのspan全てにクリックイベントハンドラをつける
	    $userRating.children('.stars').each(function(_starsIndex, starsDiv) {
	    	$(starsDiv).find('span:not(.stars-summary)').each(function(spanIndex, span) {
		        $(span).on('click', function() {
		        	// ここがクリックイベントハンドラ

					// 星の更新
					const newStarCount = spanIndex + 1;
					if(newStarCount != this_.userStars) {
						this_.dispatchAndShowStars(newStarCount);
					} else {
						this_.dispatchAndShowStars(0);
					}

					// 浮き上がる評価
					this_.overlayRatingHandler.pop(this_);

					// 三角形の評価
					if(!this_.isOmnibus) {
						this_.chartAndSummaryRatingHandler.updateRatings();
					}
		        });
			});
	    });

		// マウスをホバーすると$overlayRatingが現れるようにする
		$overallRating.mouseenter(function(e) {
			// $overallRatingに入ると
			this_.overlayRatingHandler.pop(this_);
		});
		$ratingWrap.mouseleave(function(e) {
			// $ratingsWrapから出ると
			this_.overlayRatingHandler.sleepAndFade();
		});
	}

  	get overallRatingCount() {
	    return this.othersRatingCount + (this.userStars ? 1 : 0);
  	}
  
  	get overallRatingAverage() {
	    if(this.overallRatingCount == 0) {
			return 0;
		} else {
			return (this.othersStarsTotal + this.userStars) / this.overallRatingCount;
		}
	}

  	eachOverallRating(stars) {
	    return this.eachOthersRatings[stars] + (stars == this.userStars ? 1 : 0);
	}

  	// 星の数を更新する処理
  	dispatchAndShowStars(starCount) {
		// .user-ratingの変更
		this.userRatingHandler.updateStars(starCount);
		this.userStars = starCount;

	    // .overall-ratingの変更
		this.overallRatingHandler.updateStars(this.overallRatingAverage, this.overallRatingCount);
		
		// 星の数を送信
		$.ajax({
			type: 'post',
			url: '/wp-content/themes/selva/additional/rating-post.php',
			data: {
				'course_id': this.courseId,
				'lecturer_id': this.lecturerId,
				'is_omnibus': this.isOmnibus,
				'rating_no': this.ratingNo,
				'stars': starCount
			}
		});
	}
}

const overlayRatingHandler = new OverlayRatingHandler($('.overlay-rating'));
const chartAndSummaryRatingHandler = new ChartAndSummaryRatingHandler($('.chart-rating'), $('.summary-rating'), overlayRatingHandler);

// それぞれの星の評価
// 浮き上がる評価と、全体の評価、三角形はHandlerの中で呼び出される
$('.rating-wrap').each(function() {
	new RatingWrapHandler($(this), overlayRatingHandler, chartAndSummaryRatingHandler);
});
chartAndSummaryRatingHandler.updateRatings();

})(jQuery);