(function($) {

// 検索結果ページでない
if(!$('#filter-form').length) return;

class SearchFilter {
	constructor(dataName, $appendTo={parent: undefined, child: undefined}, sortCompare={parent: undefined, child: undefined}, splitRegExp={firstSplit: /\//, parentStart: undefined, parentEnd: /\s+/, parentRepeat: undefined, childEnd: undefined, childRepeat: undefined}) {
		this.dataName = dataName;
		this.$appendTo = $appendTo;
		this.sortCompare = sortCompare;
		this.splitRegExp = splitRegExp;
		if(this.splitRegExp.firstSplit === undefined)
			this.splitRegExp.firstSplit = /\//;
		this.parentDataToSearchResultIds = {};
		this.childDataToSearchResultIds = {};

		this.createFilter();

		this.filterResults();
		if(this.$appendTo.child) this.filterResults(false);
	}

	createFilter() {
		this.parentDataToSearchResultIds = {};
		this.childDataToSearchResultIds = {};

		const this_ = this;
		$('.search-result').each(function() {
			const data = $(this).data(this_.dataName);
			if(data === undefined) return;

			const searchResultId = '#' + $(this).attr('id');

			// データをfirstSplitで分割
			const splittedData = data.split(this_.splitRegExp.firstSplit);

			// データをキー、.search-resultを値として連想配列に格納
			for(const item of splittedData) {
				// 以下、基本的にパターンが一致しない場合はそのパターン照合は無いものとして考えている

				// parentStartからparentEndまでを切り出す
				let parentStartIndex = 0;
				if(this_.splitRegExp.parentStart !== undefined) {
					// parentStartの始まりを取得
					parentStartIndex = item.search(this_.splitRegExp.parentStart);
					// parentStartの幅分進める
					if(parentStartIndex === -1) {
						parentStartIndex = 0;
					} else {
						const firstMatchedStr = item.match(this_.splitRegExp.parentStart)[0];
						parentStartIndex += firstMatchedStr.length;
					}
				}

				// 切り取る幅の取得
				let parentLength = item.length - parentStartIndex;
				if(this_.splitRegExp.parentEnd !== undefined) {
					parentLength = item.substr(parentStartIndex).search(this_.splitRegExp.parentEnd);
					if(parentLength === -1) parentLength = item.length - parentStartIndex;
				}

				// 切り取ってparentRepeatがあれば分割して代入
				let allParentData = [item.substr(parentStartIndex, parentLength)];
				if(this_.splitRegExp.parentRepeat !== undefined)
					allParentData = allParentData[0].split(this_.splitRegExp.parentRepeat);

				// 親の記録
				for(const parentData of allParentData) {
					if(parentData in this_.parentDataToSearchResultIds) {
						this_.parentDataToSearchResultIds[parentData].add(searchResultId);
					} else {
						this_.parentDataToSearchResultIds[parentData] = new Set([searchResultId]);
					}
				}

				if(this_.$appendTo.child === undefined) continue;

				// 子がある場合

				// parentEndからchildEndまでを切り出す
				let childStartIndex = parentStartIndex + parentLength;
				if(this_.splitRegExp.parentEnd !== undefined) {
					const matcheds = item.substr(childStartIndex).match(this_.splitRegExp.parentEnd);
					// parentEndの幅分進める
					if(matcheds !== null) {
						childStartIndex += matcheds[0].length;
					}
				}

				// 切り取る幅の取得
				let childLength = item.length - childStartIndex;
				if(this_.splitRegExp.childEnd !== undefined) {
					childLength = item.substr(childStartIndex).search(this_.splitRegExp.childEnd);
					if(childLength === -1) childLength = item.length - childStartIndex;
				}

				// 切り取ってparentRepeatがあれば分割して代入
				let allChildData = [item.substr(childStartIndex, childLength)];
				if(this_.splitRegExp.childRepeat !== undefined)
					allChildData = allChildData[0].split(this_.splitRegExp.childRepeat);

				// 子の記録
				for(const childData of allChildData) {
					if(childData in this_.childDataToSearchResultIds) {
						this_.childDataToSearchResultIds[childData].add(searchResultId);
					} else {
						this_.childDataToSearchResultIds[childData] = new Set([searchResultId]);
					}
				}
			}
		});

		// parentDataのform作成
		let idNo = 0;
		let orderedParentData = Object.keys(this.parentDataToSearchResultIds);
		orderedParentData.sort(this.sortCompare.parent);
		for(const parentData of orderedParentData) {
			const parentDataCount = this.parentDataToSearchResultIds[parentData].size;
			const label = parentData !== '' ? parentData : 'データなし';
			const $checkbox = $(`<div class="filter-item"><input type="checkbox" class="checkbox-${this.dataName}-parent"
					id="checkbox-${this.dataName}-parent-${idNo}" name="${this.dataName}_parent" value="${parentData}">
					<label for="checkbox-${this.dataName}-parent-${idNo}">${label}(${parentDataCount})</label></div>`);
			const this_ = this;

			// クリックイベント
			$checkbox.click(function() {
				this_.filterResults();
			});

			$checkbox.appendTo(this.$appendTo.parent);
			idNo++;
		}

		if(this_.$appendTo.child === undefined) return;

		// childDataのform作成
		idNo = 0;
		let orderedChildData = Object.keys(this.childDataToSearchResultIds);
		orderedChildData.sort(this.sortCompare.child);
		for(const childData of orderedChildData) {
			const childDataCount = this.childDataToSearchResultIds[childData].size;
			const label = childData !== '' ? childData : 'データなし';
			const $checkbox = $(`<div class="filter-item"><input type="checkbox" class="checkbox-${this.dataName}-child"
					id="checkbox-${this.dataName}-child-${idNo}" name="${this.dataName}_child" value="${childData}">
					<label for="checkbox-${this.dataName}-child-${idNo}">${label}(${childDataCount})</label></div>`);
			const this_ = this;

			// クリックイベント
			$checkbox.click(function() {
				this_.filterResults(false);
			});

			$checkbox.appendTo(this.$appendTo.child);
			idNo++;
		}
	}

	filterResults(is_parent=true) {
		let $checkeds = undefined;
		let $uncheckeds = undefined;
		let dataToSearchResultIds = undefined;
		let classInactive = undefined;
		if(is_parent) {
			$checkeds = $(`input.checkbox-${this.dataName}-parent:checked`);
			$uncheckeds = $(`input.checkbox-${this.dataName}-parent:not(:checked)`);
			dataToSearchResultIds = this.parentDataToSearchResultIds;
			classInactive = this.dataName + '-parent-inactive';
		} else {
			$checkeds = $(`input.checkbox-${this.dataName}-child:checked`);
			$uncheckeds = $(`input.checkbox-${this.dataName}-child:not(:checked)`);
			dataToSearchResultIds = this.childDataToSearchResultIds;
			classInactive = this.dataName + '-child-inactive';
		}

		if($checkeds.length) {
			// チェックがついていないやつはinactive
			$uncheckeds.each(function() {
				const selector = [...dataToSearchResultIds[$(this).val()]].join(', ');
				$(selector).addClass(classInactive);
			});

			$checkeds.each(function() {
				const selector = [...dataToSearchResultIds[$(this).val()]].join(', ');
				$(selector).removeClass(classInactive);
			});
		} else {
			// チェック無しなら全て見せる
			for(const data in dataToSearchResultIds) {
				const selector = [...dataToSearchResultIds[data]].join(', ');
				$(selector).removeClass(classInactive);
			}
		}
	}
}

new SearchFilter(
	'faculty',
	{parent: $('#faculty')}
);
new SearchFilter(
	'average_rating',
	{parent: $('#average-rating')}
);
new SearchFilter(
	'target_department',
	{parent: $('#degree-program'), child: $('#faculty-or-graduate-school-program')},
	{parent: (a, b) => a > b ? -1 : 1},
	{parentEnd: /\s+/, childEnd: /\s+/}
);
new SearchFilter(
	'semester',
	{parent: $('#semester')}
);
new SearchFilter(
	'day_and_period',
	{parent: $('#day-and-period')},
	{parent: (a, b) => {
		let aStrength = 0;
		let bStrength = 0;

		for(let i = 0; i < 2; i++) {
			const c = i ? a : b;
			let strength = 0;

			switch(c.substr(0, 1)) {
				case '日':
					strength += 10;
				case '土':
					strength += 10;
				case '金':
					strength += 10;
				case '木':
					strength += 10;
				case '水':
					strength += 10;
				case '火':
					strength += 10;
				case '月':
					strength += 10;
			}

			strength += c.substr(1, 1);

			if(i) {
				aStrength = strength;
			} else {
				bStrength = strength;
			}
		}

		return aStrength > bStrength ? +1 : -1;
	}},
	{parentRepeat: /[\/,、]/}
);
new SearchFilter(
	'campus',
	{parent: $('#campus')}
);

})(jQuery);