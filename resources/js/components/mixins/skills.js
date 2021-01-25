export default {
  computed: {
    groupedSkills() {
      return this.skills
        .reduce((acc, curr) => {
          const key = curr.category ?? 'Inne';

          if (!acc[key]) {
            acc[key] = [];
          }

          acc[key].push(curr);

          return acc;
        }, {});
    }
  }
};
