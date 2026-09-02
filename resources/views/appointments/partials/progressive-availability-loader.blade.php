function createProgressiveAvailabilityLoader(options) {
    const stages = options.stages || [
        { startOffset: 0, days: 7 },
        { startOffset: 7, days: 23 },
        { startOffset: 30, days: 60 }
    ];

    let requestVersion = 0;
    let accumulatedDates = [];

    function cancel() {
        requestVersion++;
    }

    function load(context) {
        const version = ++requestVersion;
        accumulatedDates = [];
        options.onReset?.({ context });

        function loadStage(index) {
            if (version !== requestVersion) return;

            const stage = stages[index];
            options.onProgress?.({
                context,
                stage,
                stageIndex: index,
                stageCount: stages.length,
                hasDates: accumulatedDates.length > 0
            });

            options.requestRange(context, stage)
                .done(function (response) {
                    if (version !== requestVersion) return;

                    const previousDates = new Set(accumulatedDates);
                    const responseDates = response && Array.isArray(response.dates)
                        ? response.dates.filter(date => typeof date === 'string' && date.length > 0)
                        : [];

                    accumulatedDates = Array.from(new Set(accumulatedDates.concat(responseDates))).sort();

                    options.onMerge?.({
                        context,
                        stage,
                        stageIndex: index,
                        stageCount: stages.length,
                        response: response || {},
                        newDates: responseDates.filter(date => !previousDates.has(date)),
                        allDates: accumulatedDates.slice(),
                        isFinalStage: index === stages.length - 1
                    });

                    if (index < stages.length - 1) {
                        window.setTimeout(function () {
                            loadStage(index + 1);
                        }, 0);
                        return;
                    }

                    options.onComplete?.({
                        context,
                        allDates: accumulatedDates.slice()
                    });
                })
                .fail(function (xhr) {
                    if (version !== requestVersion) return;

                    options.onError?.({
                        context,
                        stage,
                        stageIndex: index,
                        xhr,
                        allDates: accumulatedDates.slice()
                    });
                });
        }

        loadStage(0);
    }

    return {
        cancel,
        load,
        dates: function () {
            return accumulatedDates.slice();
        }
    };
}
