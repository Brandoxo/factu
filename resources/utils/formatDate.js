export const dateToMexicoLocal = () => {
const ahora = new Date();
const opciones = {
  timeZone: "America/Mexico_City",
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
  hour: "2-digit",
  minute: "2-digit",
  second: "2-digit",
  hour12: false
};

const partes = new Intl.DateTimeFormat("en-US", opciones).formatToParts(ahora);
const d = {};
partes.forEach(({ type, value }) => d[type] = value);

const fechaMexico = `${d.year}-${d.month}-${d.day}T${d.hour}:${d.minute}:${d.second}`;

console.log(fechaMexico);
return fechaMexico;
}