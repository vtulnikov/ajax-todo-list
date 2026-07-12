"use strict";

export default class Popup {
    /**статическое поле для хранения активного попапа */
    static activePopup = null;

    constructor(onConfirm) {
        this.onConfirm = onConfirm;
        this.handleOutsideClick = (e) => this.closePopupOnClickOutside(e);
        this.htmlElement = this.createElement();
    }

    createElement() {
        const popup = document.createElement("div");
        popup.classList.add("confirm-popup");
        popup.innerHTML = `
            <p>Удалить?</p>
            <div class="popup-actions">
                <button class="confirm-yes">Да</button>
                <button class="confirm-no">Нет</button>
            </div>
            `;
        return popup;
    }

    show(parentBlock) {
        Popup.activePopup?.close();
        Popup.activePopup = this;

        parentBlock.append(this.htmlElement);
        document.addEventListener("click", this.handleOutsideClick);
        //отслеживаем клики по кнопкам во всплывашке
        this.htmlElement.addEventListener("click", this.handleClick.bind(this));
    }
    close() {
        this.htmlElement.remove();
        document.removeEventListener("click", this.handleOutsideClick);

        if (Popup.activePopup === this) {
            Popup.activePopup = null;
        }
    }

    handleClick(e) {
        const confirmBtn = e.target.closest("button.confirm-yes");
        const declineBtn = e.target.closest("button.confirm-no");

        if (confirmBtn) {
            this.onConfirm();
            this.close();
        }
        if (declineBtn) {
            this.close();
        }
    }

    closePopupOnClickOutside(e) {
        if (e.target.closest(".confirm-popup")) return;
        if (e.target.closest(".action-cell")) return;

        this.close();
    }
}
