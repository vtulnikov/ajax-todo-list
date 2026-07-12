export default class Api {
    static async send(formData) {
        const response = await fetch("./ajax.php", {
            method: "POST",
            body: formData,
        });
        let json = null;
        try {
            json = await response.json();
            // console.log(json);
        } catch (e) {
            throw new Error("Некорректный json");
        }
        if (!response.ok) {
            throw new Error(
                json?.error || "Что-то пошло не так " + response.status,
            );
        }
        return json;
    }
}
