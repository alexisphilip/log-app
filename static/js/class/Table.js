class Table {
    #filterList = {};
    rowList;
    mark;

    constructor() {
        this.rowList = document.querySelectorAll(".js-log-list .js-log");
        this.mark = new Mark(document.querySelector(".js-log-list"));
    }

    setFilter(property, value) {
        this.#filterList[property] = value;
    }

    getFilter(property) {
        return this.#filterList[property] || null;
    };

    update() {

        // If there is a `search` filter, adds `<mark>` on all matched elements.
        if (typeof this.#filterList.search === "string") {
            this.mark.mark(this.#filterList.search);
        }

        for (const rowEl of this.rowList) {

            // First thing to do it show the current row, then apply the filters.
            rowEl.style.display = "table-row";

            // If there is a `originList` filter.
            if (this.#filterList.originList) {
                const show = this.#filterList.originList.includes(rowEl.dataset.origin);
                rowEl.style.display = show ? "table-row" : "none";
                // If the `originList` filter does not match the current row, do not continue to filter since we're hiding the row.
                if (!show) continue;
            }

            // If there is a `search` filter.
            if (typeof this.#filterList.search === "string") {
                // If the row has a <mark> item set by `Mark.js`.
                const hasSearchItem = !!rowEl.querySelector("mark[data-markjs]");
                rowEl.style.display = hasSearchItem ? "table-row" : "none";
                // If the `search` filter does not match the current row, do not continue to filter since we're hiding the row.
                if (!hasSearchItem) continue;
            }

            if (Object.hasOwn(this.#filterList, "dateTimeUtc")) {
                rowEl.querySelector(`[data-type="dateTimeUtc"]`).style.display = this.#filterList.dateTimeUtc ? "table-row" : "none";
            }

            if (Object.hasOwn(this.#filterList, "dateTimeInstance")) {
                const td = rowEl.querySelector(`[data-type="dateTimeInstance"]`);
                if (td) {
                    td.style.display = this.#filterList.dateTimeInstance ? "table-row" : "none";
                }
            }

            if (Object.hasOwn(this.#filterList, "dateTimeLocal")) {
                rowEl.querySelector(`[data-type="dateTimeLocal"]`).style.display = this.#filterList.dateTimeLocal ? "table-row" : "none";
            }

            if (Object.hasOwn(this.#filterList, "dateTimeEmoji")) {
                for (const emojiEl of rowEl.querySelectorAll(`[data-type="dateTimeEmoji"]`)) {
                    emojiEl.style.display = this.#filterList.dateTimeEmoji ? "table-cell" : "none";
                }
            }
        }
    }
}