class App {

    static setData(key, value) {

        let obj = {};
    
        // Parses the existing data.
        if (localStorage.logApp) {
            obj = JSON.parse(localStorage.logApp);
        }
    
        // Saves the new data.
        obj[key] = typeof value === "undefined" ? null : value;
        localStorage.logApp = JSON.stringify(obj);
    }

    static getData(key) {

        // If the local storage for the global log app does not exist, return null.
        if (!localStorage.logApp) return null;
    
        // Gets the data.
        const data = JSON.parse(localStorage.logApp)[key]
        return typeof data === "undefined" ? null : data;
    }

    static setUrlParam(key, value = null) {

        const url = new URLSearchParams(document.location.search);

        if (value) {
            url.set(key, value);
        } else {
            url.delete(key);
        }

        // For each `href` attributes in the DOM which need to have their "key" attribute updated.
        for (const linkEl of document.querySelectorAll(`a[href][data-url-params-to-update]`)) {
            if (linkEl.dataset.urlParamsToUpdate) {
                const paramToUpdateList = linkEl.dataset.urlParamsToUpdate.split(",");
                if (paramToUpdateList.includes(key)) {
                    const linkUrl = new URLSearchParams(linkEl.href);
                    if (value) {
                        linkUrl.set(key, value);
                    } else {
                        linkUrl.delete(key);
                    }
                    linkEl.href = `index.php?${url.toString()}`;
                }
            }
        }

        // Updates the URL.
        history.pushState({}, null, `index.php?${url.toString()}`);
    }

    static getUrlParam(key) {
        const url = new URLSearchParams(document.location.search);
        return url.get(key);
    }
}
