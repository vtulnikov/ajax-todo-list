export default class Model {
    static #tasks = [];

    static setTasks(data) {
        this.#tasks = Object.entries(data).map((elem) => {
            // [[id, {'message': 'task text', 'rank': 3} ]]
            return {
                id: +elem[0],
                message: elem[1].message,
                rank: elem[1].rank,
            };
        });
        this.sortTasks();
    }
    static getTasks() {
        return this.#tasks;
    }
    static updateTaskData(id, textAreaName, data) {
        const index = this.#tasks.findIndex((elem) => elem.id === id);
        this.#tasks[index][textAreaName] = data;
    }
    static sortTasks() {
        this.#tasks.sort((a, b) => {
            if (b.rank != a.rank) return b.rank - a.rank;
            //Добавляем вторую проверку, чтобы при изменении ранга, позиция элемента сохранялась
            // в том же месте после обновления страницы. А то она менялась для одинаковых рангов
            return b.id - a.id;
        });
    }
    static addNewTask(id, message, rank) {
        this.#tasks.push({ id, message, rank });
    }
    static deleteTask(id) {
        this.#tasks = this.#tasks.filter((task) => task.id != id);
    }
}