export default {
  computed: {
    groupedSkills() {
      return this.skills
        .sort((a, b) => a.priority < b.priority ? 1 : -1)
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
