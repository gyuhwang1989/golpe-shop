# golpe-shop
GOLPE Golf Equipment Online Store

GOLPE(골페) 골프용품 전문 쇼핑몰의 워드프레스 커스텀 테마 저장소입니다.
블랙 & 화이트 기반의 프리미엄 미니멀 디자인이며, WooCommerce와 함께 사용하도록 설계했습니다.

---

## 1. 폴더 구조

```
golpe-shop/
├─ README.md
├─ .gitignore                      ← 비밀번호·DB·업로드 파일이 GitHub에 올라가지 않도록 차단
└─ wp-content/
   └─ themes/
      └─ golpe/                    ← 워드프레스 테마 폴더 (이 폴더를 사이트에 넣습니다)
         ├─ style.css              ← 테마 정보
         ├─ functions.php          ← 기능 파일 불러오기
         ├─ screenshot.png         ← 관리자 테마 목록에 보이는 미리보기
         ├─ header.php / footer.php
         ├─ front-page.php         ← 메인 홈페이지
         ├─ index.php / page.php / single.php / 404.php / searchform.php
         ├─ inc/
         │  ├─ setup.php           ← 메뉴, 로고, 썸네일 등 기본 기능
         │  ├─ enqueue.php         ← CSS / JS 불러오기
         │  ├─ template-tags.php   ← 아이콘, 링크 도우미 함수
         │  ├─ categories.php      ← 골프백 / 골프웨어 / 액세서리 카테고리 정의
         │  ├─ customizer.php      ← 관리자 화면에서 문구·이미지·사업자 정보 수정
         │  └─ woocommerce.php     ← WooCommerce 활성화 시에만 동작하는 연동 코드
         ├─ template-parts/
         │  ├─ home/               ← 메인 화면 섹션 (배너, 카테고리, 신상품 등)
         │  ├─ content.php
         │  └─ content-none.php
         └─ assets/
            ├─ css/main.css        ← 전체 디자인
            ├─ css/woocommerce.css ← 쇼핑(상품·장바구니·결제) 화면 디자인
            └─ js/main.js          ← 모바일 메뉴, 검색창
```

## 2. 로컬(내 컴퓨터) 개발 환경 설치 — Windows + Local

> 실제 운영 사이트가 아니라 **내 컴퓨터의 테스트 사이트**에서 먼저 확인합니다.

### 2-1. Local에서 워드프레스 사이트 만들기
1. **Local** 프로그램을 실행합니다.
2. 왼쪽 아래 **+ (Create a new site)** → **Create a new site** → Continue
3. 사이트 이름: `golpe` 입력 → Continue
4. 환경: **Preferred** 선택 → Continue
5. 관리자 아이디·비밀번호·이메일 입력 → **Add Site**
   - 이 비밀번호는 GitHub나 다른 곳에 올리지 마세요.
6. 설치가 끝나면 **WP Admin** 버튼으로 관리자 화면에 들어갈 수 있습니다.

### 2-2. 테마 넣기
1. Local에서 사이트 이름 오른쪽의 **Go to site folder** 를 누릅니다.
2. `app\public\wp-content\themes` 폴더로 이동합니다.
   - 보통 경로: `C:\Users\내이름\Local Sites\golpe\app\public\wp-content\themes`
3. 이 저장소의 `wp-content\themes\golpe` 폴더를 위 `themes` 폴더 안에 **복사**합니다.
   - GitHub 사용법이 익숙해지면 복사 대신 저장소를 직접 연결하는 방법(아래 5번)을 사용하세요.
4. 관리자 화면 → **외모 → 테마** → **GOLPE** → **활성화**

### 2-3. WooCommerce 설치 (쇼핑 기능)
1. 관리자 화면 → **플러그인 → 새로 추가**
2. `WooCommerce` 검색 → **지금 설치** → **활성화**
3. 설정 마법사는 일단 건너뛰어도 됩니다. (나중에 결제·배송 설정)
   - 통화는 **WooCommerce → 설정 → 일반 → 통화: 대한민국 원(₩)** 으로 맞춰 주세요.

### 2-4. 상품 카테고리 만들기
**상품 → 카테고리** 에서 아래 **슬러그**(영문 주소)로 만들면 메인 화면·메뉴와 자동으로 연결됩니다.
(만들지 않아도 화면은 정상 표시되며, 링크는 전체상품 페이지로 연결됩니다.)

| 이름 | 슬러그 | 상위 카테고리 |
|---|---|---|
| 골프백 | `golf-bags` | 없음 |
| 캐디백 | `caddie-bags` | 골프백 |
| 스탠드백 | `stand-bags` | 골프백 |
| 보스턴백 | `boston-bags` | 골프백 |
| 골프웨어 | `golf-wear` | 없음 |
| 의류 | `apparel` | 골프웨어 |
| 모자 | `caps` | 골프웨어 |
| 신발 | `shoes` | 골프웨어 |
| 액세서리 | `accessories` | 없음 |
| 파우치 | `pouches` | 액세서리 |
| 골프 액세서리 | `golf-accessories` | 액세서리 |
| 기타 용품 | `etc` | 액세서리 |

- 카테고리 편집 화면의 **썸네일** 이미지를 넣으면 메인 카테고리 타일에 사진이 표시됩니다. (세로형 3:4 권장)
- 카테고리 구성은 `inc/categories.php` 한 곳에서 관리합니다.

### 2-5. 화면 문구·이미지 바꾸기 (코드 수정 없이)
**외모 → 사용자 정의하기 → GOLPE 테마 설정**
- **메인 화면**: 상단 공지, 메인 배너 제목·설명·버튼·이미지, 브랜드 소개 문구
- **사업자 정보 (푸터)**: 상호, 대표자, 사업자등록번호, 통신판매업 신고번호, 주소, 고객센터, 이메일
  - 입력한 항목만 푸터에 표시됩니다.

### 2-6. 메뉴 (선택)
**외모 → 메뉴** 에서 메뉴를 만들고 위치를 **메인 메뉴** 로 지정하면 그 메뉴가 사용됩니다.
지정하지 않으면 카테고리 기반 기본 메뉴가 자동으로 표시됩니다.

## 3. 실행(확인) 방법
1. Local에서 사이트 **Start site**
2. **Open site** → 메인 화면 확인
3. 브라우저 창 크기를 줄이거나 개발자 도구(F12)의 모바일 보기로 반응형 확인

## 4. 작업 규칙 (ChatGPT · Claude 공동 작업)
- `main` 브랜치는 직접 수정하지 않습니다.
- Claude는 `claude/작업이름`, ChatGPT는 `chatgpt/작업이름` 브랜치에서 작업하고 PR(변경 요청)로 올립니다.
- 사장님이 PR을 확인·승인한 뒤 `main`에 합칩니다.
- 비밀번호, API 키, 결제 정보, `wp-config.php`, 데이터베이스 파일은 절대 올리지 않습니다. (`.gitignore`로 차단)
- 상품·주문·고객 데이터는 임의로 초기화하지 않습니다.
- 운영 사이트에 반영하기 전에 Local 테스트 사이트에서 먼저 확인합니다.

## 5. (나중에) Local 사이트와 저장소 직접 연결하기
복사 대신 연결하면, 브랜치를 바꿀 때 테스트 사이트에 바로 반영됩니다.
GitHub Desktop 설치가 필요하므로, 진행 전에 단계별로 다시 안내합니다.
