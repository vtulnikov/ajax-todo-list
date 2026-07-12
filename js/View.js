export default class View {
    static container = document.getElementById("container");
    static tbody = this.container.querySelector("tbody");
    static addTask = document.getElementById("add-task");
    static loader = document.getElementById("loader");

    static confirmCreatePopup = document.getElementById(
        "confirm-creation-popup",
    );

    static createTable(data, startNumber = 1) {
        this.tbody.innerHTML = "";

        let count = startNumber;
        //=====TODO=====
        //HtmlFragment - разобраться и сделать через него, чтобы не добавлять построчно в DOM
        for (let elem of data) {
            const tr = document.createElement("tr");
            tr.dataset.id = elem.id;
            //раньше тут еще была textarea, но мы ее теперь создаем динамически при дблклике и потом удаляем после сохранения в activeEditor
            tr.innerHTML = `
                <td>${count}</td>
                <td class="task">
                    <p>${elem.message}</p>
                </td>
                <td class="rank">
                    <p class="rank-badge rank-${elem.rank}">${elem.rank}</p>
                </td>
                <td class="delete"><div class="action-cell"><a href="#"><img src="./images/trash-bin-icon.png" /></a></div></td>
            `;
            this.tbody.appendChild(tr);
            count++;
        }
    }
    static addError(input) {
        input.classList.add("error");
    }
    static removeError(input) {
        if (input.classList.contains("error")) input.remove("error");
    }
    static createConfirmPopup() {
        const div = document.createElement("div");
        div.setAttribute("id", "confirm-creation-popup");
        const p = document.createElement("p");
        p.textContent = "Задача успешно добавлена";
        div.append(p);
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 1500);
    }
    static createPagination(tasksCount, perPage, currentPage = 1) {
        const pagination = document.querySelector(".pagination");
        if (pagination) pagination.remove();

        const pages = Math.ceil(tasksCount / perPage);
        const div = document.createElement("div");
        div.classList.add("pagination");
        for (let i = 1; i <= pages; i++) {
            const a = document.createElement("a");
            a.setAttribute("href", `#`);
            a.textContent = i;
            if (i == currentPage) a.classList.add("active");
            div.append(a);
        }
        this.container.insertAdjacentElement("beforeend", div);
    }
}
