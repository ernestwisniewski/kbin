export default function getIdFromElement(element) {
    return element.id.substring(element.id.lastIndexOf("-") + 1);
}
