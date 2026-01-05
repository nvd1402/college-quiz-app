import appStyles from '~styles/App.module.css';
import styles from '~styles/CreateModel.module.css';

import { useMutation } from '@tanstack/react-query';
import { SyntheticEvent } from 'react';
import { FiSave } from 'react-icons/fi';
import { RxCross2 } from 'react-icons/rx';
import { apiCreateDocument, apiUpdateDocument } from '~api/document';
import Loading from '~components/Loading';
import useLanguage from '~hooks/useLanguage';
import { Document } from '~models/document';
import createFormUtils from '~utils/create-form-utils';
import css from '~utils/css';

type CreateDocumentProps = {
    onMutateSuccess: () => void;
    setShowPopUp: React.Dispatch<React.SetStateAction<boolean>>;
    data?: Document; // For update mode
};

export default function CreateDocument({
    onMutateSuccess,
    setShowPopUp,
    data
}: CreateDocumentProps) {
    const isEditMode = !!data;
    const language = useLanguage('component.create_document');
    const handleClosePopUp = () => {
        setShowPopUp(false);
    };
    const formUtils = createFormUtils(styles);
    const handleCreateDocument = async (e: SyntheticEvent<HTMLFormElement, SubmitEvent>) => {
        e.preventDefault();
        document.querySelector(`.${styles.formData}`)?.querySelectorAll<HTMLInputElement>('input[name], textarea[name]').forEach(node => {
            node.classList.remove('error');
            formUtils.getParentElement(node)?.removeAttribute('data-error');
        });
        const submitter = e.nativeEvent.submitter as HTMLButtonElement;
        const form = e.target as HTMLFormElement;
        const formData = new FormData(form);
        try {
            if (isEditMode && data) {
                await apiUpdateDocument(formData, data.id);
            } else {
                await apiCreateDocument(formData);
            }
            await new Promise(resolve => setTimeout(resolve, 100));
            await onMutateSuccess();
            if (submitter.name === 'save') handleClosePopUp();
            else if (!isEditMode) form.reset();
        } catch (error) {
            formUtils.showFormError(error);
        }
    };
    const { mutate, isPending } = useMutation({
        mutationFn: handleCreateDocument
    });
    return (
        <div className={
            css(
                styles.createModelContainer,
            )
        }>
            {
                isPending ? <Loading /> : null
            }
            <div className={
                css(
                    styles.createModelForm,
                )
            }>
                <div className={styles.header}>
                    <h2 className={styles.title}>{isEditMode ? (language?.edit || 'Chỉnh sửa tài liệu') : (language?.create || 'Tạo tài liệu')}</h2>
                    <div className={styles.escButton}
                        onClick={handleClosePopUp}
                    >
                        <RxCross2 />
                    </div>
                </div>
                <div className={styles.formContent}>
                    <form onSubmit={(e: SyntheticEvent<HTMLFormElement, SubmitEvent>) => {
                        mutate(e);
                    }}
                        onInput={(e) => { formUtils.handleOnInput(e); }}
                        className={styles.formData}
                        encType="multipart/form-data">
                        <div className={styles.groupInputs}>
                            <div className={styles.wrapItem}>
                                <label className={appStyles.required} htmlFor='title'>{language?.title || 'Tiêu đề'}</label>
                                <input
                                    id='title'
                                    name='title'
                                    className={css(appStyles.input, styles.inputItem)}
                                    type='text' 
                                    defaultValue={data?.title}
                                    required />
                            </div>
                            <div className={styles.wrapItem}>
                                <label htmlFor='description'>{language?.description || 'Mô tả'}</label>
                                <textarea
                                    id='description'
                                    name='description'
                                    className={css(appStyles.input, styles.inputItem)}
                                    rows={3}
                                    defaultValue={data?.description || ''} />
                            </div>
                            <div className={styles.wrapItem}>
                                <label className={isEditMode ? undefined : appStyles.required} htmlFor='file'>{language?.file || 'File PDF'}</label>
                                <input
                                    id='file'
                                    name='file'
                                    className={css(appStyles.input, styles.inputItem)}
                                    type='file'
                                    accept='application/pdf'
                                    required={!isEditMode} />
                                {isEditMode && (
                                    <small style={{ color: 'var(--color-text-secondary)', marginTop: '5px', display: 'block' }}>
                                        Để trống nếu không muốn thay đổi file
                                    </small>
                                )}
                            </div>
                        </div>
                        <div className={styles.actionItems}>
                            <button name='save'
                                className={
                                    css(
                                        appStyles.actionItem,
                                        isPending ? appStyles.buttonSubmitting : ''
                                    )
                                }>
                                <FiSave />{language?.save || 'Lưu'}
                            </button>
                            {!isEditMode && (
                                <button name='save-more'
                                    className={
                                        css(
                                            appStyles.actionItemWhite,
                                            isPending ? appStyles.buttonSubmitting : ''
                                        )
                                    }>
                                    <FiSave />{language?.saveMore || 'Lưu và thêm mới'}
                                </button>
                            )}
                        </div>
                    </form>
                </div>
            </div >
        </div >
    );
}

