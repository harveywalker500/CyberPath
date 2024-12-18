const { fetchUserProgress, renderUserProgressChart } = require('../js/analytics');

describe("fetchUserProgress", () => {
    global.fetch = jest.fn(() =>
        Promise.resolve({
            json: () => Promise.resolve([{ userName: "John", progressDate: "2024-03-01", episodesCompleted: 3, storiesCompleted: 2 }])
        })
    );

    test("Fetch user progress data successfully", async () => {
        const data = await fetchUserProgress("week");
        expect(data).toEqual([{ userName: "John", progressDate: "2024-03-01", episodesCompleted: 3, storiesCompleted: 2 }]);
    });
});

describe("renderUserProgressChart", () => {
    document.body.innerHTML = `<canvas id="userProgressChart"></canvas>`;
    
    test("Render chart with provided data", () => {
        const mockData = [
            { userName: "John", progressDate: "2024-03-01", episodesCompleted: 3, storiesCompleted: 2 },
            { userName: "Jane", progressDate: "2024-03-02", episodesCompleted: 2, storiesCompleted: 1 },
        ];
        
        expect(() => renderUserProgressChart(mockData, "week")).not.toThrow();
    });
});
