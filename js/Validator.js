export default class Validator {
    static validate(formData) {
        for (let [key, value] of formData) {
            if (key === "rank") {
                return value > 10 || value < 1 ? false : true;
            } else if (key === "message") {
                return value.length < 10 ? false : true;
            } else {
                return false;
            }
        }
    }
}
