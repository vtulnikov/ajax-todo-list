import Model from "./Model.js";
import View from "./View.js";
import Api from "./Api.js";
import Validator from "./Validator.js";
import Popup from "./Popup.js";
import Config from "./Config.js";

export default class Controller {
    /**создаем объект, в котором будем хранить id строки, текст и textarea. Это удобно, т.к. всегда в любой момент мы
     * можем получить эти данные из этого объекта, вместо того, чтобы получать их из DOM
     */
    static #activeEditor = {};

    static async init() {
        // Model.setTasks(window.TODOS);
        await this.getTasks(Config.offset, Config.perpage);
        View.createTable(Model.getTasks());

        const count = await this.#countTasks();
        await this.#createPagination(count); 
        await this.#bindEvents();
    }
    static async #bindEvents() {
        this.addListenerToPagination();

        //Добавление задачи
        View.addTask.addEventListener("submit", async (e) => {
            e.preventDefault();
            

            const formData = new FormData(e.target);
            if (!Validator.validate(formData)) {
                //====TODO====
                //допилить валидацию
                // this.classList.add("error");
                console.log("Не прошло валидацию!");
                return;
            }
            formData.append("action", "create");

            try {
                View.loader.classList.add("loading");
                const result = await Api.send(formData);
                if (result?.success) {
                    await this.getTasks(Config.offset, Config.perpage);
                    View.createTable(Model.getTasks(), Config.startNumber);
                    
                    const count = await this.#countTasks();
                    await this.#createPagination(count); 
                    e.target.reset();
                    setTimeout(() => View.createConfirmPopup(), 200);
                }
            } catch (e) {
                console.log(
                    "Произошла ошбика при создании задачи " + e.message,
                );
                //сохраняем данные в поле, если ошибка
                View.addTask.value = formData.get('message');
            } finally {
                View.loader.classList.remove("loading");
            }
        });
        /** ===== TODO =====
         * добавить удаление красной рамки после начала ввода текста в поле
         */
        View.addTask.addEventListener("input", (e) => {
            
        });

        View.tbody.addEventListener("dblclick", (e) => {
            //если что-то есть в объекте состояния, то делаем закрытие textarea и пр.
            if (
                this.#activeEditor != null &&
                Object.keys(this.#activeEditor).length != 0
            ) {
                this.#closeEditor();
            }

            const td = e.target.closest("td");
            const tr = e.target.closest("tr");
            if (!td || !tr) return;

            const id = +tr.dataset.id;
            const p = td.querySelector("p");
            if (!p) return;

            const allTds = Array.from(tr.querySelectorAll("td"));
            //находим индекс кликнутой ячейки
            const index = allTds.indexOf(td);

            const textArea = document.createElement("textarea");

            if (index === Config.messageColumn) {
                textArea.setAttribute("name", "message");
            } else if (index === Config.rankColumn) {
                textArea.setAttribute("name", "rank");
            } else {
                //если клик не по первым двум ячейкам - это не то, что нам нужно
                return;
            }
            td.append(textArea);

            this.#openEditor(id, p, textArea);
        });
        View.tbody.addEventListener("click", (e) => {
            const delLink = e.target.closest("a");
            if (!delLink) return;
            e.preventDefault();

            const td = delLink.closest("td");
            const actionCell = td.querySelector(".action-cell");
            const confirmDeletePopup = new Popup(() => {
                const id = delLink.closest("tr").dataset.id;
                if (!id) return;
                this.deleteTask(id);
            });
            confirmDeletePopup.show(actionCell);
        });

        document.addEventListener("keydown", (e) => {
            const key = e.key;
            if (key !== "Enter") return;
            const { textarea } = this.#activeEditor;
            /**просто убираем фокус, чтобы сработало событие  onblur, на которое мы повесили
             * this.#closeEditor(); ниже в #openEditor и удаляем textarea
             * если здесь тоже делать textarea.remove();, то выдает ошибку в консоли
            */
            textarea?.blur();
        });
    }
    static #openEditor(id, p, textarea) {
        p.classList.add("hidden");
        textarea.classList.remove("hidden");
        textarea.focus();

        textarea.value = p.textContent;
        //добавляем данные в activeEditor
        this.#activeEditor = { id, p, textarea };
        //мы сохранили все данные, включая textarea в объект, теперь можно ее удалить после закрытия
        textarea.onblur = () => {
            this.#closeEditor();
            textarea.remove();
        };
    }
    //====TODO===
    /**Слишком много метод делает: и окно закрывает и модель обновляет и данные отправляет */
    static async #closeEditor() {
        //защита от повторного вызова closeEditor() при событии keydown/onblur
        //получаем все из #activeEditor и обнуляем его
        const editor = this.#activeEditor;
        if (!editor) return;

        const { id, p, textarea } = editor;
        this.#activeEditor = null;

        p.classList.remove("hidden");

        if (p.textContent === textarea.value) return;

        const newValue =
            textarea["name"] === "rank" ? parseInt(textarea.value) : textarea.value;
        p.textContent = newValue;

        const formData = new FormData();
        formData.append("id", id);
        formData.append([textarea["name"]], newValue);
        formData.append("action", "update");

        View.loader.classList.add("loading");
        try {
            const result = await Api.send(formData);
            if (result?.success) {
                Model.updateTaskData(id, textarea["name"], newValue);
                if (textarea["name"] === "rank") {
                    await this.getTasks(Config.offset, Config.perpage);
                    View.createTable(Model.getTasks(), Config.startNumber);
                }
            }
        } catch (e) {
            console.log("Произошла ошибка" + e.message);
        } finally {
            View.loader.classList.remove("loading");
        }
        p.textContent = textarea.value;
    }

    static addListenerToPagination() {
        View.container.addEventListener("click", async (e) => {
            const pagination = e.target.closest(".pagination");
            if (!pagination) return;

            const link = e.target.closest("a");
            if (!link) return;
            e.preventDefault();

            const activeLink = pagination.querySelector(".active");
            if (activeLink) activeLink.classList.remove("active");
            link.classList.add("active");

            const pageNumber = parseInt(link.textContent);
            //сохранаяем текущую страницу
            Config.currentPage = pageNumber;

            const offset = pageNumber * Config.perpage - Config.perpage;
            const startNumber = offset + 1;
            await this.getTasks(offset, Config.perpage, {showloader: false});
            //обновляем значение сдвига, чтобы получать правильное значения на нашей странице
            Config.offset = offset;
            Config.startNumber = startNumber;
            View.createTable(Model.getTasks(), startNumber);
        })
    }
    static async getTasks(offset, perpage, {showloader = true} = {}) {
        // не будем показывать showloader при переходе по пагинации
        if (showloader) {
            View.loader.classList.add("loading")
        }
        
        const formData = new FormData();
        formData.append("action", "get");
        formData.append("offset", offset);
        formData.append("perpage", perpage);
        try {
            const result = await Api.send(formData);
            Model.setTasks(result);
        } catch (e) {
            console.log("Произошла ошибка" + e.message);
        } finally {
            View.loader.classList.remove("loading");
        }
    }
    static async #countTasks() {
        const formData = new FormData();
        formData.append("action", "count");
        try {
            const result = await Api.send(formData);
            if (!result["count"]) throw new Error("Поле count не найдено");
            return result["count"];
            
        } catch (e) {
            console.log("Произошла ошибка" + e.message);
        }
    }
    static async #createPagination(count) {
        //формируем пагинацию только если страниц больше 1
        if ((count / Config.perpage) > 1)
                View.createPagination(count, Config.perpage, Config.currentPage);
    }
    static async deleteTask(id) {
        View.loader.classList.add("loading");

        const formData = new FormData();
        formData.append("id", id);
        formData.append("action", "delete");

        try {
            const result = await Api.send(formData);
            //====TODO====
            //добавить спиннер после отправки данных
            if (result?.success) {
                const count = await this.#countTasks();
                Config.offset = Config.offset === count ? Config.offset - Config.perpage : Config.offset;
                //нужно обновить текущую страницу и стартовое значение списка, если удалили последнюю задачу на странице
                Config.currentPage = Math.ceil(count / Config.perpage);
                Config.startNumber = Config.offset + 1;

                await this.getTasks(Config.offset, Config.perpage);
                await this.#createPagination(count); 
                View.createTable(Model.getTasks(), Config.startNumber, Config.currentPage);
            }
        } catch (e) {
            console.log("Произошла ошибка при удалении задачи " + e.message);
        } finally {
            View.loader.classList.remove("loading");
        }
    }
}
