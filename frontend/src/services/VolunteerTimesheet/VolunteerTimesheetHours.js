import axios from 'axios'
import store from '../../store'
import moment from 'moment';

export default async(data) => {
    let responseData = [];
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/timesheet?page=" + data.page + "&type=" + data.type;
    await axios({
        url: url,
        method: 'get',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then(({data}) => {

          if (data.data) {

              if (data.data) {
                  let timeData = data.data
                  timeData.filter((toItem, toIndex) => {
                      let timeSheet = timeData[toIndex].timesheet;

                      timeSheet.filter((timeSheetItem, timeSheetIndex) => {

                          let momentObj = moment(timeData[toIndex].timesheet[timeSheetIndex].date_volunteered, 'MM-DD-YYYY');
                          let dateVolunteered = momentObj.format('YYYY-MM-DD');
                          data.data[toIndex].timesheet[timeSheetIndex]['date'] = moment(dateVolunteered).format('D')
                          data.data[toIndex].timesheet[timeSheetIndex]['year'] = moment(dateVolunteered).format('YYYY')
                          data.data[toIndex].timesheet[timeSheetIndex]['month'] = moment(dateVolunteered).format('M')
                      });
                  });
              }
          }

          responseData = data
      })
      .catch(function() {});
    return responseData;
}