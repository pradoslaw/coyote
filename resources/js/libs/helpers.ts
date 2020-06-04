Array.prototype.keyBy = function (key: string) {
  return this.reduce((data, item) => {
    data[item[key]] = item;

    return data;
  }, {});
};
