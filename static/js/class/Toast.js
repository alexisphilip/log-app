/**
 * Builds a Toast UI component, and appends it to the DOM.
 * Interface of Bootstrap's Toast class.
 * @see https://getbootstrap.com/docs/5.3/components/toasts/
 */
class Toast {
    el;
    bootstrapObject;
    #toast;
    #toastPropertyList;
    #header;
    #headerPropertyList;
    #title;
    #body;
    #bodyPropertyList;
    #content;
    #timeout = 5000;

    toast(content, propertyList) {
        this.#toast = content;
        this.#toastPropertyList = propertyList;
        return this;
    }

    header(content, propertyList) {
        this.#header = content;
        this.#headerPropertyList = propertyList;
        return this;
    }

    title(content) {
        this.#title = content;
        return this;
    }

    body(content, propertyList) {
        this.#body = content;
        this.#bodyPropertyList = propertyList;
        return this;
    }

    content(content) {
        this.#content = content;
        return this;
    }

    timeout(milliseconds) {
        this.#timeout = milliseconds;
        return this;
    }

    /**
     * Inits the Bootstrap's Toast and appends the Toast element to the DOM.
     * @see https://getbootstrap.com/docs/5.3/components/toasts/#usage
     */
    display() {

        // Clones the toast template from the DOM.
        this.el = document.querySelector(".template-list .js-toast").cloneNode(true);

        // ###############################
        // SETS TOAST CONTENT & PROPERTIES
        // ###############################

        if (this.#toast) {
            this.el.innerHTML = this.#toast;
        }

        // Appends optional style.
        if (this.#toastPropertyList?.style) {
            for (const [key, value] of Object.entries(this.#toastPropertyList?.style)) {
                this.el.style.setProperty(key, value);
            }
        }

        // Appends optional classes.
        if (this.#toastPropertyList?.class) {
            this.el.classList.add(...this.#toastPropertyList.class.split(" "));
        }

        // ################################
        // SETS HEADER CONTENT & PROPERTIES
        // ################################

        if (this.#header) {
            this.el.querySelector(".js-toast-header").innerHTML = this.#header;
        } else if (this.#title) {
            this.el.querySelector(".js-toast-title").innerHTML = this.#title;
        }

        // Appends optional style.
        if (this.#headerPropertyList?.style) {
            for (const [key, value] of Object.entries(this.#headerPropertyList?.style)) {
                this.el.querySelector(".js-toast-header").style.setProperty(key, value);
            }
        }

        // Appends optional classes.
        if (this.#headerPropertyList?.class) {
            this.el.querySelector(".js-toast-header").classList.add(...this.#headerPropertyList.class.split(" "));
        }

        // ##############################
        // SETS BODY CONTENT & PROPERTIES
        // ##############################

        if (this.#body) {
            this.el.querySelector(".js-toast-body").innerHTML = this.#body;
        } else if (this.#content) {
            this.el.querySelector(".js-toast-content").innerHTML = this.#content;
        }

        // Appends optional style.
        if (this.#bodyPropertyList?.style) {
            for (const [key, value] of Object.entries(this.#bodyPropertyList?.style)) {
                this.el.querySelector(".js-toast-body").style.setProperty(key, value);
            }
        }

        // Appends optional classes.
        if (this.#bodyPropertyList?.class) {
            this.el.querySelector(".js-toast-body").classList.add(...this.#bodyPropertyList.class.split(" "));
        }

        // ####################################
        // INITS & APPENDS THE TOAST TO THE DOM
        // ####################################

        document.querySelector(".js-toast-container").appendChild(this.el);

        this.bootstrapObject = new bootstrap.Toast(this.el, {
            delay: this.#timeout
        });

        this.bootstrapObject.show();
    }
}
