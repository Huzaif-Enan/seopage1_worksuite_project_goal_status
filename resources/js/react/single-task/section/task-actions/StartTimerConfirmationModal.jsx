import React from 'react'
import Modal from '../../components/Modal'
import Button from '../../components/Button'

const StartTimerConfirmationModal = ({isOpen, onConfirm}) => {
  const [buttonVisible, setButtonVisible] = React.useState(false);
  const [countDown, setCountDown] = React.useState(20);

  React.useEffect(() => {
    let count = countDown ?? 0;

    let timeIntervelId = setInterval(() => {
        setCountDown(count--);
    }, 1000);


    let timeOutId = setTimeout(() => {
      setButtonVisible(true);
      clearInterval(timeIntervelId)
    }, 22000);

    

    return () => {
      clearTimeout(timeOutId);
      clearInterval(timeIntervelId);
    };
  }, []);


  return (
    <Modal isOpen={isOpen} className="subtask-timer-confirmation--modal">
        <div className='subtask-timer-confirmation--panel'>
           <div className="subtask-timer-confirmation--content">
                <h4 className='mb-3'> Do You Understand The Following Things? </h4>
                
                <ol type='A'>
                    <li> Your job is not to decide what the look and feel of a website will be based on a few reference websites. </li>
                    <li>Your job is not to research a theme based on an instruction shared by the PM.</li>
                    <li>Your job is not to research a plugin based on a problem shared by PM.</li>
                    <li>Your job is not to choose the color scheme of a website.</li>
                    <li>Your job is not to talk to the support for example the Shopify support team, theme support, plugin support and any other support for any solution.</li>
                    <li>Your job is not to create the training videos for the client after the completion of a project.</li>
                    <li>You understand that all your hours have to be logged/tracked, and you will be questioned if you don’t log at least 7 hours for any reason.</li>
                </ol>


                <p>In general, anything that has to do with requirements define (of any sort) has to be done by the project manager. Your job is to execute the work based on the defined requirements. </p> 

                <p>If for any reason, project manager needs your help for any of those things, he will have to create a separate task for each of them and those tasks have to be authorized by the top management mandatorily. Report immediately if you are asked to do any of these and if it wasn’t authorized by top management. You should see a text like “Authorized by top management” on the right side of the task title if it was authorized.</p>
                
                <p> In case, you don’t report, the extra time taken for these will be considered as your lacking (as they will remain unaccountable) and you will receive negative performance score.”</p>

                <div className='d-flex align-items-center'>
                     <Button 
                        onClick={onConfirm} 
                        className='ml-auto'
                        disabled={!buttonVisible}
                      > 
                        Yes, I Fully Understand This {!buttonVisible && `(${countDown})`}
                      </Button> 
                </div>
           </div>
        </div>
    </Modal>
  )
}

export default StartTimerConfirmationModal