const formatToTwoDigits = nr => '0'.repeat(Math.max(0, 2 - String(nr)).length) + nr;

export const formatISOTime = txt => time => {
  let format = txt("timeformat");
  const date = new Date(time);

  format = format.replace(/H/, formatToTwoDigits(date.getHours()));
  format = format.replace(/i/, formatToTwoDigits(date.getMinutes()));
  format = format.replace(/s/, formatToTwoDigits(date.getSeconds()));

  return format;
};

export const formatISODate = txt => time => {
  let format = txt("dateformat");
  const date = new Date(time);

  format = format.replace(/Y/, date.getFullYear());
  format = format.replace(/m/, formatToTwoDigits(date.getMonth() + 1));
  format = format.replace(/d/, formatToTwoDigits(date.getDate()));

  return format;
};
