# api.py

from flask import Flask, request, jsonify
import joblib
import torch
import torch.nn as nn # <-- THÊM IMPORT NÀY
from transformers import BertTokenizer, BertModel # <-- THÊM IMPORT NÀY
import numpy as np
import os
import json

# =========================================================================
# BƯỚC 1: DÁN BẢN THIẾT KẾ (CLASS DEFINITION) CỦA MÔ HÌNH VÀO ĐÂY
# =========================================================================
class BertClassifier(nn.Module):
    """
    Mô hình phân loại kết hợp BERT cho văn bản và các lớp Linear cho đặc trưng số.
    """
    def __init__(self, freeze_bert=False):
        super(BertClassifier, self).__init__()
        HOTEL_INFO_DIM = 7
        REVIEW_INFO_DIM = 3
        HOTEL_EMBED_DIM = 32
        REVIEW_EMBED_DIM = 16
        # Số lớp đầu ra, cần định nghĩa một giá trị mặc định. 
        # Giá trị thực sẽ được nạp từ mô hình đã lưu.
        NUM_CLASSES = 6 

        self.bert = BertModel.from_pretrained('trituenhantaoio/bert-base-vietnamese-uncased')
        self.hotel_info_embedder = nn.Linear(HOTEL_INFO_DIM, HOTEL_EMBED_DIM)
        self.review_info_embedder = nn.Linear(REVIEW_INFO_DIM, REVIEW_EMBED_DIM)

        D_in = 768 + HOTEL_EMBED_DIM + REVIEW_EMBED_DIM
        H = 128
        D_out = NUM_CLASSES

        self.classifier = nn.Sequential(
            nn.Linear(D_in, H),
            nn.ReLU(),
            nn.Dropout(0.2),
            nn.Linear(H, D_out)
        )
        if freeze_bert:
            for param in self.bert.parameters():
                param.requires_grad = False

    def forward(self, hotel_info, review_info, input_ids, attention_mask):
        outputs = self.bert(input_ids=input_ids, attention_mask=attention_mask)
        last_hidden_state_cls = outputs[0][:, 0, :]
        hotel_embedding = self.hotel_info_embedder(hotel_info)
        review_embedding = self.review_info_embedder(review_info)
        combined_features = torch.cat([last_hidden_state_cls, hotel_embedding, review_embedding], dim=1)
        logits = self.classifier(combined_features)
        return logits

# =========================================================================

# --- KHỞI TẠO ỨNG DỤNG FLASK ---
app = Flask(__name__)

# --- TẢI MÔ HÌNH VÀ CÁC THÀNH PHẦN ---
print("--- Đang tải các mô hình và tài nguyên... ---")
MODEL_DIR = 'production_model'
# ... (các đường dẫn file giữ nguyên) ...
TOKENIZER_PATH = os.path.join(MODEL_DIR, 'bert_tokenizer')
SCALER_HOTEL_PATH = os.path.join(MODEL_DIR, 'scaler_hotel.pkl')
SCALER_REVIEW_PATH = os.path.join(MODEL_DIR, 'scaler_review.pkl')
MODEL_PATH = os.path.join(MODEL_DIR, 'bert_classifier_model.pkl')
LABEL_MAP_PATH = os.path.join(MODEL_DIR, 'label_map.json')

try:
    tokenizer = BertTokenizer.from_pretrained(TOKENIZER_PATH)
    scaler_hotel = joblib.load(SCALER_HOTEL_PATH)
    scaler_review = joblib.load(SCALER_REVIEW_PATH)
    
    # joblib.load() bây giờ sẽ tìm thấy class BertClassifier và tải thành công
    model = joblib.load(MODEL_PATH)
    model.eval()
    
    with open(LABEL_MAP_PATH, 'r') as f:
        label_map = json.load(f)
    
    print("--- Tải thành công! API đã sẵn sàng. ---")
except Exception as e:
    print(f"!!! Lỗi khi tải mô hình: {e}")
    tokenizer = model = scaler_hotel = scaler_review = label_map = None

# --- ENDPOINT DỰ ĐOÁN (giữ nguyên không đổi) ---
@app.route('/predict', methods=['POST'])
def predict():
    # ... (toàn bộ code của hàm predict giữ nguyên) ...
    if not request.is_json:
        return jsonify({"error": "Yêu cầu phải là JSON"}), 400
    data = request.get_json()
    comment = data.get('comment', '')
    hotel_info = data.get('hotel_info', [])
    review_info = data.get('review_info', [])
    if not comment or not hotel_info or not review_info:
        return jsonify({"error": "Thiếu dữ liệu đầu vào: comment, hotel_info, review_info"}), 400
    try:
        scaled_hotel_info = scaler_hotel.transform(np.array(hotel_info).reshape(1, -1))
        scaled_review_info = scaler_review.transform(np.array(review_info).reshape(1, -1))
        encoded_comment = tokenizer.encode_plus(
            text=comment, add_special_tokens=True, max_length=512,
            padding='max_length', truncation=True, return_attention_mask=True, return_tensors='pt'
        )
        input_ids = encoded_comment['input_ids']
        attention_mask = encoded_comment['attention_mask']
        hotel_tensor = torch.tensor(scaled_hotel_info, dtype=torch.float32)
        review_tensor = torch.tensor(scaled_review_info, dtype=torch.float32)
        with torch.no_grad():
            logits = model(
                hotel_info=hotel_tensor, review_info=review_tensor,
                input_ids=input_ids, attention_mask=attention_mask
            )
        prediction_index = torch.argmax(logits, dim=1).flatten().item()
        predicted_score = label_map.get(str(prediction_index), "N/A")
        return jsonify({
            "predicted_score": predicted_score,
            "prediction_index": prediction_index
        })
    except Exception as e:
        print(f"Lỗi trong quá trình dự đoán: {e}")
        return jsonify({"error": "Đã xảy ra lỗi trong quá trình xử lý"}), 500

# --- CHẠY ỨNG DỤNG (giữ nguyên không đổi) ---
if __name__ == '__main__':
    app.run(debug=True, port=5000)